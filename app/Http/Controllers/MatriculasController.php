<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Matricula;


class MatriculasController extends Controller {




	public function postMatricularuno()
	{
		$user = User::fromToken();
		$alumno_id 		= Request::input('alumno_id');
		$grupo_id 		= Request::input('grupo_id');
		$year_id 		= Request::input('year_id');
		$year_id 		= Request::input('year_id');
		return Matricula::matricularUno($alumno_id, $grupo_id, $year_id, $user->user_id);
	}



	public function postMatricularEn()
	{
		$user = User::fromToken();
		$alumno_id 		= Request::input('alumno_id');
		$grupo_id 		= Request::input('grupo_id');
		$year_id 		= Request::input('year_id');

		$consulta = 'SELECT m.id, m.alumno_id, m.grupo_id, m.estado, g.year_id 
			FROM matriculas m 
			inner join grupos g 
				on m.alumno_id = :alumno_id and g.year_id = :year_id and m.grupo_id=g.id and m.grupo_id=:grupo_id and m.deleted_at is null';

		$matriculas = DB::select($consulta, ['alumno_id'=>$alumno_id, 'year_id'=>$year_id, 'grupo_id'=>$grupo_id]);

		if (count($matriculas) > 0) {
			return 'Ya matriculado';
		}

		return Matricula::matricularUno($alumno_id, $grupo_id, $year_id, $user->user_id);
	}


	public function putReMatricularuno()
	{
		$user = User::fromToken();
		$matricula_id 	= Request::input('matricula_id');
		
		$matri 			= Matricula::findOrFail($matricula_id);
		$matri->estado 	= 'MATR';
		$matri->save();

		return $matri;
	}



	public function putSetAsistente()
	{
		$user = User::fromToken();

		$alumno_id 		= Request::input('alumno_id');
		$matricula_id 	= Request::input('matricula_id');
		
		$matricula 				= Matricula::findOrFail($matricula_id);
		$matricula->estado 		= 'ASIS';
		$matricula->save();

		return $matricula;
	}


	public function putSetNewAsistente()
	{
		$user = User::fromToken();

		$alumno_id 	= Request::input('alumno_id');
		$grupo_id 	= Request::input('grupo_id');

		$matricula = new Matricula;
		$matricula->alumno_id 	= $alumno_id;
		$matricula->grupo_id	= $grupo_id;
		$matricula->estado 		= 'ASIS';
		$matricula->save();


		return $alumno;
	}



	public function putCambiarFechaRetiro()
	{
		$user = User::fromToken();

		$matricula_id = Request::input('matricula_id');
		$fecha_retiro = Request::input('fecha_retiro');
		
		$matricula 					= Matricula::findOrFail($matricula_id);
		$matricula->fecha_retiro 	= $fecha_retiro;
		$matricula->save();

		return $matricula;
	}


	public function putCambiarFechaMatricula()
	{
		$user = User::fromToken();

		$matricula_id = Request::input('matricula_id');
		$fecha_matricula = Request::input('fecha_matricula');
		
		$matricula 					= Matricula::findOrFail($matricula_id);
		$matricula->fecha_matricula = $fecha_matricula;
		$matricula->save();

		return $matricula;
	}


	public function putAlumnosGradoAnterior()
	{
		//$user = User::fromToken();

		$grupo_actual 	= Request::input('grupo_actual');
		$grado_ant_id 	= Request::input('grado_ant_id');
		$year_ant 		= Request::input('year_ant');
		$year_ant_id	= null;
		
		if (!$grupo_actual) {
			return;
		}

		$sqlYearAnt = 'SELECT id from years where year=:year_ant';
		
		$year_cons = DB::select($sqlYearAnt, [ ':year_ant'	=> $year_ant ]);
		if (count($year_cons) > 0) {
			$year_ant_id = $year_cons[0]->id;
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
		
		// Alumnos desertores o retirados del grupo
		$sql2 = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
							m.grupo_id, 
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre,
							m.fecha_retiro as fecha_retiro, m.estado, m.fecha_matricula 
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id and m.grupo_id=:grupo_id2 and (m.estado="RETI" or m.estado="DESE")
						left join users u on a.user_id=u.id and u.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where a.deleted_at is null and m.deleted_at is null
						order by a.apellidos, a.nombres';

		// Alumnos del grado anterior que no se han matriculado en este grupo
		$sql3 = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
							m.grupo_id, 
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre,
							m.fecha_retiro as fecha_retiro, m.estado, m.fecha_matricula 
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id 
						inner join grupos gru on gru.id=m.grupo_id and gru.year_id=:year_id
						inner join grados gra on gra.id=:grado_id and gru.grado_id=gra.id
						left join users u on a.user_id=u.id and u.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where a.deleted_at is null and m.deleted_at is null and m.alumno_id
							not in (SELECT m.alumno_id FROM alumnos a 
								inner join matriculas m on a.id=m.alumno_id and m.grupo_id=:grupo_id3 
								where a.deleted_at is null and m.deleted_at is null)
						order by a.apellidos, a.nombres';

		$consulta = '('.$sql1.') UNION ('.$sql2.') UNION ('.$sql3.')';


		$res = DB::select($consulta, [ ':grupo_id'	=> $grupo_actual['id'], 
									':grupo_id2'	=> $grupo_actual['id'], 
									':year_id'		=> $year_ant_id, 
									':grado_id'		=> $grado_ant_id, 
									':grupo_id3'	=> $grupo_actual['id'] ]);

		return $res;

	}




	public function putRetirar()
	{
		$user 	= User::fromToken();
		$id 	= Request::input('matricula_id');

		$matri 	= Matricula::findOrFail($id);
		$matri->estado 			= 'RETI';
		$matri->fecha_retiro 	= Request::input('fecha_retiro');
		$matri->save();

		return $matri;
	}

	public function putDesertar()
	{
		$user 	= User::fromToken();
		$id 	= Request::input('matricula_id');

		$matri 	= Matricula::findOrFail($id);
		$matri->estado 			= 'DESE';
		$matri->fecha_retiro 	= Request::input('fecha_retiro');
		$matri->save();

		return $matri;
	}


	public function deleteDestroy($id)
	{
		$user = User::fromToken();
		$matri = Matricula::findOrFail($id);
		$matri->estado = 'RETI';
		$matri->delete();
		return $matri;
	}

}