<?php namespace App\Http\Controllers\Actividades;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\WsActividad;
use App\Models\WsRespuesta;
use App\Models\WsActividadResuelta;


class RespuestasController extends Controller {


	public function putActividad()
	{
		$user = User::fromToken();

		$datos 				= [];
		$actividad_id 		= Request::input('actividad_id');

		$actividad = WsActividad::datosActividad($actividad_id);


		$consulta = 'SELECT a.id as asignatura_id, a.grupo_id, a.profesor_id, a.creditos, a.orden,
						m.materia, m.alias as alias_materia, 
						p.id as profesor_id, p.nombres as nombres_profesor, p.apellidos as apellidos_profesor,
						p.foto_id, IFNULL(i.nombre, IF(p.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre
					FROM asignaturas a 
					inner join materias m on m.id=a.materia_id and m.deleted_at is null
					inner join profesores p on p.id=a.profesor_id and p.deleted_at is null 
					inner join grupos g on g.id=a.grupo_id and g.year_id=? and g.deleted_at is null 
					inner join matriculas mt on mt.grupo_id=a.grupo_id and mt.deleted_at is null 
					left join images i on p.foto_id=i.id and i.deleted_at is null
					where a.deleted_at is null
					order by a.orden, m.orden';

		$alumnos = DB::select($consulta, [$user->year_id]);



		$datos['alumnos'] 		= $alumnos;
		$datos['actividad'] 	= $actividad;

		return $datos;

	}


}