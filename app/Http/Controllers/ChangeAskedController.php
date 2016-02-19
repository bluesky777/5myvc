<?php namespace App\Http\Controllers;


use Request;
use DB;

use App\Models\User;
use App\Models\ChangeAsked;
use App\Models\Alumno;

use Carbon\Carbon;
use \DateTime;


class ChangeAskedController extends Controller {


	public function getToMe()
	{
		$user = User::fromToken();

		// toca quitar los campos somebody, ya que esta consulta solo será para buscar los pedidos que han hecho alumnos.

		$consulta = 'SELECT c.id, c.asked_by_user_id, c.comentario_pedido, c.main_image_id, c.oficial_image_id, c.nombres as nombres_asked,
						c.apellidos as apellidos_asked, c.somebody_id, c.somebody_nombres, c.somebody_apellidos, 
						c.somebody_nota_id, c.somebody_nota_old, c.somebody_nota_new, c.somebody_image_id_to_delete, c.materia_to_remove_id, c.materia_to_add_id,
						c.asked_nota_id, c.nota_old, c.nota_new, c.rechazado_at, c.accepted_at, c.periodo_asked_id, c.year_asked_id, c.created_at,
						c.deleted_at, c.deleted_by,
						u.tipo, a.id as alumno_id, a.nombres, a.apellidos, 
						IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre, 
						i2.nombre as foto_nombre_asked,
						i3.nombre as somebody_imagen_nombre_to_delete
					FROM change_asked c
					inner join users u on u.id=c.asked_by_user_id
					inner join alumnos a on a.user_id=u.id
					left join images i on i.id=a.foto_id and i.deleted_at is null
					left join images i2 on i2.id=c.oficial_image_id and i2.deleted_at is null
					left join images i3 on i3.id=c.somebody_image_id_to_delete and i3.deleted_at is null
					ORDER BY c.id DESC LIMIT 10';

		$cambios = DB::select($consulta);
		return $cambios;
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

	public function destroy($id)
	{
		//
	}

}