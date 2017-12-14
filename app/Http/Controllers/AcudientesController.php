<?php namespace App\Http\Controllers;



use Request;
use DB;

use App\Models\User;
use App\Models\Acudiente;
use App\Models\Parentesco;


class AcudientesController extends Controller {

	public function putDatos()
	{
		
		$grupo_actual 	= Request::input('grupo_actual');
		return $grupo_actual;
		if (!$grupo_actual) {
			return;
		}


		// Alumnos asistentes o matriculados del grupo
		$sql1 = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
							m.grupo_id, 
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre,
							m.fecha_retiro as fecha_retiro, m.estado, m.fecha_matricula 
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id and m.grupo_id=:grupo_id and (m.estado="ASIS" or m.estado="MATR")
						left join users u on a.user_id=u.id and u.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where a.deleted_at is null and m.deleted_at is null
						order by a.apellidos, a.nombres';
		
		$res = DB::select($consulta, [ ':grupo_id'	=> $grupo_actual['id'], 
									':grupo_id2'	=> $grupo_actual['id'], 
									':year_id'		=> $year_ant_id, 
									':grado_id'		=> $grado_ant_id, 
									':grupo_id3'	=> $grupo_actual['id'] ]);

		return $res;
	}

	public function putBuscar()
	{
		$termino 	= Request::input('termino');

		$consulta = 'SELECT ac.id, ac.nombres, ac.apellidos, ac.sexo, ac.fecha_nac, ac.ciudad_nac, ac.telefono, pa.parentesco, ac.user_id, 
							ac.celular, ac.ocupacion, ac.email, ac.barrio, ac.direccion, ac.tipo_doc, ac.documento, ac.created_by, ac.updated_by, ac.created_at, ac.updated_at, 
							ac.foto_id, IFNULL(i.nombre, IF(ac.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
							u.username, u.is_active
						FROM parentescos pa
						left join acudientes ac on ac.id=pa.acudiente_id and ac.deleted_at is null
						left join users u on ac.user_id=u.id and u.deleted_at is null
						left join images i on i.id=ac.foto_id and i.deleted_at is null
						WHERE ac.nombres like "%:termino%" and pa.deleted_at is null';

		$res = DB::select($consulta, [ ':termino' => $termino ]);

		return $res;
	}


	public function postCrear()
	{
		try {
			$acudiente = new Acudiente;
			$acudiente->nombres		=	Request::input('nombres');
			$acudiente->apellidos	=	Request::input('apellidos');
			$acudiente->sexo		=	Request::input('sexo');
			$acudiente->user_id		=	Request::input('user_id');
			$acudiente->tipo_doc	=	Request::input('tipo_doc');
			$acudiente->documento	=	Request::input('documento');
			$acudiente->ciudad_doc	=	Request::input('ciudad_doc');
			$acudiente->telefono	=	Request::input('telefono');
			$acudiente->celular		=	Request::input('celular');
			$acudiente->ciudad_doc	=	Request::input('ocupacion');
			$acudiente->email		=	Request::input('email');

			$acudiente->save();

			$parentesco = new Parentesco;
			$parentesco->acudiente_id		=	$acudiente->id;
			$parentesco->alumno_id			=	Request::input('alumno_id');
			$parentesco->parentesco			=	Request::input('parentesco')['parentesco'];
			$parentesco->observaciones		=	Request::input('observaciones');
			$parentesco->save();


			$consulta = 'SELECT ac.id, ac.nombres, ac.apellidos, ac.sexo, ac.fecha_nac, ac.ciudad_nac, ac.telefono, pa.parentesco, ac.user_id, 
					ac.celular, ac.ocupacion, ac.email, ac.barrio, ac.direccion, ac.tipo_doc, ac.documento, ac.created_by, ac.updated_by, ac.created_at, ac.updated_at, 
					ac.foto_id, IFNULL(i.nombre, IF(ac.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
					u.username, u.is_active
				FROM parentescos pa
				left join acudientes ac on ac.id=pa.acudiente_id and ac.deleted_at is null
				left join users u on ac.user_id=u.id and u.deleted_at is null
				left join images i on i.id=ac.foto_id and i.deleted_at is null
				WHERE ac.id=? and pa.deleted_at is null';

			$acudiente = DB::select($consulta, [ $acudiente->id ]);

			return (array) $acudiente[0];
		} catch (Exception $e) {
			return $e;
		}
	}


	public function update($id)
	{
		$acudiente = Acudiente::findOrFail($id);
		try {
			$acudiente->nombres		=	Request::input('nombres');
			$acudiente->apellidos	=	Request::input('apellidos');
			$acudiente->sexo		=	Request::input('sexo');
			$acudiente->user_id		=	Request::input('user_id');
			$acudiente->tipo_doc	=	Request::input('tipo_doc');
			$acudiente->documento	=	Request::input('documento');
			$acudiente->ciudad_doc	=	Request::input('ciudad_doc');
			$acudiente->telefono	=	Request::input('telefono');
			$acudiente->celular		=	Request::input('celular');
			$acudiente->ciudad_doc	=	Request::input('ocupacion');
			$acudiente->email		=	Request::input('email');


			$acudiente->save();
		} catch (Exception $e) {
			return $e;
		}
	}


	public function destroy($id)
	{
		$acudiente = Acudiente::findOrFail($id);
		$acudiente->delete();

		return $acudiente;
	}

}