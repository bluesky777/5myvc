<?php namespace App\Http\Controllers\Actividades;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\Actividad;
use App\Models\Grupo;


class MisActividadesController extends Controller {


	public function putDatos()
	{
		$user = User::fromToken();

		$datos 				= [];
		$mis_asignaturas 	= [];
		$alumno_id 			= Request::input('alumno_id');

		if (!$alumno_id) {
			$alumno_id = $user->persona_id;
		}

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
					where mt.alumno_id=? and a.deleted_at is null
					order by a.orden, m.orden';

		$mis_asignaturas = DB::select($consulta, [$user->year_id, $alumno_id]);

		$cant = count($mis_asignaturas);

		for ($i=0; $i < $cant; $i++) { 
			
			$consulta 			= 'SELECT * FROM ws_actividades a WHERE a.asignatura_id=? and a.deleted_at is null and a.periodo_id=?';
			$actividades 		= DB::select($consulta, [ $mis_asignaturas[$i]->asignatura_id, $user->periodo_id ]);
			$mis_asignaturas[$i]->actividades = $actividades;

		}


		$datos['mis_asignaturas'] = $mis_asignaturas;

		return $datos;

	}

	public function putMiActividad()
	{
		$user 	= User::fromToken();
		
		$datos 	= [];

		$consulta 			= 'SELECT * FROM ws_actividades a WHERE a.id=? ';
		$actividad 			= DB::select($consulta, [ Request::input('actividad_id') ])[0];
		
		$datos['actividad'] = $actividad;
		
		return $datos;
	}

	public function putGuardar()
	{
		$user 	= User::fromToken();

		$act = Actividad::findOrFail(Request::input('id'));

		$act->descripcion	=	Request::input('descripcion');
		$act->compartida	=	Request::input('compartida');
		$act->can_upload	=	Request::input('can_upload');
		$act->tipo			=	Request::input('tipo');
		$act->in_action		=	Request::input('in_action');
		$act->duracion_preg	=	Request::input('duracion_preg');
		$act->duracion_exam	=	Request::input('duracion_exam');
		$act->oportunidades	=	Request::input('oportunidades');
		$act->one_by_one	=	Request::input('one_by_one');
		$act->puntaje_por_promedio	=	Request::input('puntaje_por_promedio');
		$act->contenido		=	Request::input('contenido');
		$act->inicia_at		=	Request::input('inicia_at_str');
		$act->finaliza_at	=	Request::input('finaliza_at_str');
		$act->save();

		return $act;
	}

	public function deleteDestroy($id)
	{
		$act = Actividad::findOrFail($id);
		$act->delete();

		return $act;
	}

}