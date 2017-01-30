<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Matricula;


class CarteraController extends Controller {






	public function putSoloDeudores()
	{
		
		$year_id 	= Request::input('year_id');


		$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion, a.pazysalvo, a.deuda,
							m.grupo_id, 
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
							u.username, u.is_superuser, u.is_active, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre,
							m.fecha_retiro as fecha_retiro, m.estado, m.fecha_matricula, 
							gr.nombre as nombre_grupo, gr.abrev as abrev_grupo, gr.titular_id, gr.orden as orden_grupo
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id and (m.estado="ASIS" or m.estado="MATR")
						inner join grupos gr on gr.id=m.grupo_id and gr.year_id=:year_id 
						left join users u on a.user_id=u.id and u.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where a.deleted_at is null and m.deleted_at is null and gr.deleted_at is null 
							and a.pazysalvo=false
						order by gr.orden, a.apellidos, a.nombres';


		$res = DB::select($consulta, [ ':year_id'	=> $year_id ]);

		return $res;
	}


	public function putAlumnos()
	{
		//$user = User::fromToken();

		$grupo_actual 	= Request::input('grupo_actual');
		



		$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion, a.pazysalvo, a.deuda,
							m.grupo_id, 
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
							u.username, u.is_superuser, u.is_active, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre,
							m.fecha_retiro as fecha_retiro, m.estado, m.fecha_matricula 
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id and m.grupo_id=:grupo_id and (m.estado="ASIS" or m.estado="MATR")
						left join users u on a.user_id=u.id and u.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where a.deleted_at is null and m.deleted_at is null
						order by a.apellidos, a.nombres';


		$res = DB::select($consulta, [ ':grupo_id'	=> $grupo_actual['id'] ]);

		return $res;

	}




}