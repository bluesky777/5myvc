<?php namespace App\Http\Controllers;


use Request;
use DB;

use App\Models\User;
use App\Models\ChangeAsked;
use App\Models\ChangeAskedDetails;
use App\Models\Alumno;

use Carbon\Carbon;
use \DateTime;


class ChangeAskedController extends Controller {


	public function getToMe()
	{
		$user = User::fromToken();

		$cambios = [];
		$cambios_elim = [];

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
						inner join grupos g on g.id=m.grupo_id and g.year_id=3 and g.deleted_at is null
						inner join users u on a.user_id=u.id and u.deleted_at is null
						inner join change_asked c on c.asked_by_user_id=u.id and c.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where a.deleted_at is null and m.deleted_at is null
						order by a.apellidos, a.nombres';

			$cambios_alum = DB::select($consulta);

			# Luego haré los profesores ....

			return [ 'alumnos'=>$cambios_alum, 'profesores'=>[] ];

		}elseif ($user->tipo == 'Profesor') {

			$consulta = 'SELECT c.id, c.asked_by_user_id, c.comentario_pedido, c.main_image_id, c.oficial_image_id,
						c.rechazado_at, c.accepted_at, c.periodo_asked_id, c.year_asked_id, c.created_at,
						c.deleted_at, c.deleted_by,
						u.tipo, a.id as alumno_id, a.nombres, a.apellidos, 
						IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre, 
						i2.nombre as foto_nombre_asked,
						i3.nombre as somebody_imagen_nombre_to_delete,
						u2.username, u2.tipo
					FROM change_asked c
					inner join users u on u.id=c.asked_by_user_id
					inner join alumnos a on a.user_id=u.id
					inner join matriculas m on a.id=m.alumno_id and m.deleted_at is null
					inner join grupos g on m.grupo_id=g.id and g.titular_id=? and g.deleted_at is null
					left join images i on i.id=a.foto_id and i.deleted_at is null
					left join images i2 on i2.id=c.oficial_image_id and i2.deleted_at is null
					left join images i3 on i3.id=c.somebody_image_id_to_delete and i3.deleted_at is null
					left join users u2 on u2.id=c.deleted_by and u2.deleted_at is null
					ORDER BY c.id DESC LIMIT 20';

			$cambios = DB::select($consulta, [$user->persona_id]);

		}
		
		
		$respuesta = ['cambios'=>$cambios, 'cambios_elim'=>$cambios_elim];
		return $respuesta;
	}


	
	public function putVerDetalles(){
		$user 		= User::fromToken();
		$asked_id 	= Request::input('asked_id');
		$detalles 	= ChangeAskedDetails::detalles($asked_id);
		return [ 'detalles' => $detalles ];
	}



	public function putAceptar()
	{
		$user = User::fromToken();

		$id = Request::input('asked_id');
		$comentario = Request::input('comentario');
		$dt = Carbon::now()->format('Y-m-d G:H:i');


		$consulta = '';
		$cambio = DB::table('change_asked')->select('*')->where('id', $id)->first();

		if ($cambio->oficial_image_id) {
			$this->cambiarOficialAlumno($cambio);
		}


		$asked = DB::table('change_asked')
			->where('id', $id)
			->update(['accepted_at' => $dt,
						'deleted_at' => $dt,
						'comentario_respuesta' => $comentario,
						'deleted_by' => $user->id]);

		return ['deleted_at' => $dt,
				'comentario_respuesta' => $comentario,
				'deleted_by' => $user->id];
	}



	public function putRechazar()
	{
		$user = User::fromToken();

		$id = Request::input('asked_id');
		$comentario = Request::input('comentario');
		$dt = Carbon::now()->format('Y-m-d G:H:i');


		$consulta = '';
		$cambio = DB::table('change_asked')->select('*')->where('id', $id)->first();

		$asked = DB::table('change_asked')
			->where('id', $id)
			->update(['rechazado_at' => $dt,
						'deleted_at' => $dt,
						'comentario_respuesta' => $comentario,
						'deleted_by' => $user->id]);

		return ['deleted_at' => $dt,
				'comentario_respuesta' => $comentario,
				'deleted_by' => $user->id];
	}


	public function cambiarOficialAlumno($cambio)
	{
		$alumno = Alumno::where('user_id', $cambio->asked_by_user_id)->first();

		if ($alumno) {

			$alumno->foto_id = $cambio->oficial_image_id;
			$alumno->save();
			return $alumno;
		}else{
			abort(400, 'No se encuentra alumno para este usuario.');
		}

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





	public function destroy($id)
	{
		//
	}

}