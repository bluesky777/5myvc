<?php namespace App\Http\Controllers;


use Request;
use DB;

use App\Models\User;
use App\Models\ChangeAsked;
use App\Models\ChangeAskedDetails;
use App\Models\Alumno;
use App\Models\Profesor;
use App\Models\Acudiente;
use App\Models\Year;
use App\Models\Debugging;
use App\Models\ImageModel;

use Carbon\Carbon;
use \DateTime;


class ChangeAskedController extends Controller {


	public function getToMe()
	{
		$user = User::fromToken();


		// toca quitar los campos somebody, ya que esta consulta solo será para buscar los pedidos que han hecho alumnos.
		if ($user->tipo == 'Usuario' && $user->is_superuser) {

			$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, u.username, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
							m.grupo_id, m.estado, g.nombre as grupo_nombre, g.abrev as grupo_abrev,
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre,
							c.id as asked_id, c.asked_by_user_id, c.asked_to_user_id
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id and m.deleted_at is null
						inner join grupos g on g.id=m.grupo_id and g.year_id=? and g.deleted_at is null
						inner join users u on a.user_id=u.id and u.deleted_at is null
						inner join change_asked c on c.asked_by_user_id=u.id and c.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where c.answered_by is null and a.deleted_at is null and m.deleted_at is null
						order by a.apellidos, a.nombres';

			$cambios_alum = DB::select($consulta, [$user->year_id]);

			# Luego haré los profesores ....

			return [ 'alumnos'=>$cambios_alum, 'profesores'=>[] ];

		}elseif ($user->tipo == 'Profesor') {

			$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, u.username, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
							m.grupo_id, m.estado, g.nombre as grupo_nombre, g.abrev as grupo_abrev,
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre,
							c.id as asked_id, c.asked_by_user_id, c.asked_to_user_id
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id and m.deleted_at is null
						inner join grupos g on g.id=m.grupo_id and g.year_id=? and g.titular_id=? and g.deleted_at is null
						inner join users u on a.user_id=u.id and u.deleted_at is null
						inner join change_asked c on c.asked_by_user_id=u.id and c.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where c.answered_by is null and a.deleted_at is null and m.deleted_at is null
						order by a.apellidos, a.nombres';

			$cambios_alum = DB::select($consulta, [$user->year_id, $user->persona_id]);
			
			return [ 'alumnos'=>$cambios_alum, 'profesores'=>[] ];
		}
		
		return ['msg'=> 'No puedes ver pedidos'];
	}


	
	public function putVerDetalles(){
		$user 		= User::fromToken();
		$asked_id 	= Request::input('asked_id');
		$detalles 	= ChangeAskedDetails::detalles($asked_id);
		return [ 'detalles' => $detalles ];
	}



	public function putAceptarAlumno()
	{
		$user 			= User::fromToken();

		$asked_id 		= Request::input('asked_id');
		$tipo 			= Request::input('tipo');
		$data_id 		= Request::input('data_id');
		$valor_nuevo 	= Request::input('valor_nuevo');

		$pedido 		= ChangeAsked::pedido($asked_id);

		if ($tipo == "img_perfil") {
			$this->cambiarImgAlumno($pedido);
			$consulta = 'UPDATE change_asked_data SET image_id_accepted=true WHERE id=:data_id';
			DB::select($consulta, [ ':data_id' => $data_id ]);
			$pedido->image_id_accepted 	= true;
		}

		if ($tipo == "foto_oficial") {
			$this->cambiarOficialAlumno($pedido);
			$consulta = 'UPDATE change_asked_data SET foto_id_accepted=true WHERE id=:data_id';
			DB::select($consulta, [ ':data_id' => $data_id ]);
			$pedido->foto_id_accepted 	= true;
		}
		
		if ($tipo == "img_delete") {
			ImageModel::eliminar_imagen_y_enlaces($pedido->image_to_delete_id);
			$consulta = 'UPDATE change_asked_data SET image_to_delete_accepted=true WHERE id=:data_id';
			DB::select($consulta, [ ':data_id' => $data_id ]);
			$pedido->image_to_delete_accepted 	= true;
		}

		$finalizado = $this->finalizar_si_no_hay_cambios($pedido, $user->user_id);

		return [ 'finalizado'=> $finalizado, 'msg'=>'Cambio aceptado con éxito'];
	}



	public function putRechazar()
	{
		$user 		= User::fromToken();

		$asked_id 	= Request::input('asked_id');
		$tipo 		= Request::input('tipo');
		$data_id 	= Request::input('data_id');

		//$dt = Carbon::now()->format('Y-m-d G:H:i');
		$pedido 	= ChangeAsked::pedido($asked_id);

		if ($tipo == "img_perfil") {
			$consulta = 'UPDATE change_asked_data SET image_id_accepted=false WHERE id=:data_id';
			DB::select($consulta, [ ':data_id' => $data_id ]);
			$pedido->image_id_accepted 	= false;
		}

		if ($tipo == "foto_oficial") {
			$consulta = 'UPDATE change_asked_data SET foto_id_accepted=false WHERE id=:data_id';
			DB::select($consulta, [ ':data_id' => $data_id ]);
			$pedido->foto_id_accepted 	= false;
		}

		if ($tipo == "img_delete") {
			$consulta = 'UPDATE change_asked_data SET image_to_delete_accepted=false WHERE id=:data_id';
			DB::select($consulta, [ ':data_id' => $data_id ]);
			$pedido->image_to_delete_accepted 	= false;
		}

		$finalizado = $this->finalizar_si_no_hay_cambios($pedido, $user->user_id);

		return [ 'finalizado'=> $finalizado, 'msg'=>'Cambio rechazado con éxito'];
	}


	public function cambiarOficialAlumno($pedido)
	{
		$alumno = Alumno::where('user_id', $pedido->asked_by_user_id)->first();
		$alumno->foto_id = $pedido->foto_id_new;
		$alumno->save();
		return $alumno;
		
	}


	public function cambiarImgAlumno($pedido)
	{
		$usu = User::findOrFail($pedido->asked_by_user_id);
		$usu->imagen_id = $pedido->image_id_new;
		$usu->save();
		return $usu;
		
	}

	public function finalizar_si_no_hay_cambios($pedido, $user_id)
	{
		if ( ($pedido->pazysalvo_new===null 	or $pedido->pazysalvo_accepted!==null) and
			($pedido->foto_id_new===null 		or $pedido->foto_id_accepted!==null) and
			($pedido->image_id_new===null 		or $pedido->image_id_accepted!==null) and
			($pedido->firma_id_new===null 		or $pedido->firma_id_accepted!==null) and
			($pedido->image_to_delete_id===null or $pedido->image_to_delete_accepted!==null)
			) 
		{
			$dt = Carbon::now()->format('Y-m-d G:H:i');
			$consulta = 'UPDATE change_asked SET answered_by=:user_id, updated_by=:user_id2, updated_at=:dt WHERE id=:asked_id';
			DB::update($consulta, [ ':user_id' => $user_id, ':user_id2' => $user_id, ':dt' => $dt, ':asked_id' => $pedido->asked_id ]);
			return true;
		}

		return false;
		
	}




	public function putSolicitarCambios()
	{
		$user = User::fromToken();

		$tipo 	= Request::input('tipo');
		$id 	= Request::input('persona_id');
		
		if ($tipo == 'Al') {
			$alumno = Alumno::where('id', $id)->first();

			/*
			$consulta = 'SELECT c.id, c.asked_by_user_id, c.asked_to_user_id, c.asked_to_user_id, c.comentario_pedido, 
						a.id as alumno_id, a.nombres as nombres_alum, a.apellidos as apellidos_alum,
						c.rechazado_at, c.accepted_at, c.periodo_asked_id, c.year_asked_id, c.created_at,
						c.deleted_at, c.deleted_by, u.tipo
					FROM change_asked c
					inner join users u on u.id=c.asked_by_user_id
					left join alumnos a on a.user_id=u.id
					WHERE c.deleted_at is null';
			$cambios = DB::select($consulta);
			*/

			$cambios = [];

			if (($alumno->nombres != Request::input('nombres')) && Request::input('nombres')) {
				$cambios['nombres'] = Request::input('nombres');
			}

			if (($alumno->apellidos != Request::input('apellidos')) && Request::input('apellidos')) {
				$cambios['apellidos'] = Request::input('apellidos');
			}

			if (($alumno->sexo != Request::input('sexo')) && Request::input('sexo')) {
				$cambios['sexo'] = Request::input('sexo');
			}

			if (($alumno->fecha_nac != Request::input('fecha_nac')) && Request::input('fecha_nac')) {
				$fecha_nac_new = Request::input('fecha_nac');
				$fecha_nac_old = $alumno->fecha_nac->format('Y-m-d');

				if ($fecha_nac_new != $fecha_nac_old) {
					$cambios['fecha_nac'] = $fecha_nac_new;
					$cambios['fecha_nac_old'] = $fecha_nac_old;
				}

			}

			if (Request::has('ciudad_nac')) {
				$ciudad_id = null;

				if (Request::input('ciudad_nac')['id']) {
					$ciudad_id = Request::input('ciudad_nac')['id'];
				}else{
					$ciudad_id = Request::input('ciudad_nac');
				}
				if (($alumno->ciudad_nac != $ciudad_id) && $ciudad_id) {
					$cambios['ciudad_nac'] = $ciudad_id;
				}
			}
			

			return $cambios;

		}


	}





	public function putDestruir()
	{
		$user 			= User::fromToken();
		$asked_id 		= Request::input('asked_id');
		$data_id 		= Request::input('data_id');
		$assignment_id 	= Request::input('assignment_id');

		$consulta = 'DELETE FROM change_asked WHERE id=:asked_id';
		$borrar = DB::delete($consulta, [ ':asked_id' => $asked_id ]);
		
		$consulta = 'DELETE FROM change_asked_data WHERE id=:data_id';
		$borrar = DB::delete($consulta, [ ':data_id' => $data_id ]);
		
		$consulta = 'DELETE FROM change_asked_assignment WHERE id=:assignment_id';
		$borrar = DB::delete($consulta, [ ':assignment_id' => $assignment_id ]);
		


		return [ 'borrar' => $borrar ];
	}

}