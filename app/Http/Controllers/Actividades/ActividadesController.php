<?php namespace App\Http\Controllers\Actividades;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\WsActividad;
use App\Models\Grupo;


class ActividadesController extends Controller {


	public function postCrear()
	{
		$user = User::fromToken();

		$acti 					= new WsActividad;
		$acti->asignatura_id 	= Request::input('asignatura_id');
		$acti->periodo_id 		= $user->periodo_id;
		$acti->created_by 		= $user->user_id;
		$acti->save();

		return $acti;
	}

	public function putDatos()
	{
		$user = User::fromToken();

		$datos 				= [];
		$mis_asignaturas 	= [];
		$otras_asignaturas 	= [];
		$grupo_id 			= Request::input('grupo_id');

		$consulta = 'SELECT * FROM grupos g WHERE g.year_id=? and g.deleted_at is null';
		$grupos = DB::select($consulta, [$user->year_id]);
		$datos['grupos'] = $grupos;

		if ($user->is_superuser) {
			if ($grupo_id) {
			 	$otras_asignaturas = Grupo::detailed_materias( $grupo_id );
			}elseif (Request::input('asign_id')) { // Si en vez del grupo_id me dan la asignatura, tengo que averiguar el grupo_id
				$consulta = 'SELECT a.grupo_id FROM asignaturas a WHERE a.id=? and a.deleted_at is null';
				$grupo_id = DB::select($consulta, [Request::input('asign_id')])[0]->grupo_id;
				
				$otras_asignaturas = Grupo::detailed_materias( $grupo_id );
			}
			
		}

		if ($user->tipo == 'Profesor') {
			if ($grupo_id) {
				$mis_asignaturas = Grupo::detailed_materias( $grupo_id, $user->persona_id );
			 	$otras_asignaturas = Grupo::detailed_materias( $grupo_id );
			}elseif (Request::input('asign_id')) { // Si en vez del grupo_id me dan la asignatura, tengo que averiguar el grupo_id
				$consulta = 'SELECT a.grupo_id FROM asignaturas a WHERE a.id=? and a.deleted_at is null';
				$grupo_id = DB::select($consulta, [Request::input('asign_id')])[0]->grupo_id;
				
				$mis_asignaturas = Grupo::detailed_materias( $grupo_id, $user->persona_id );
				$otras_asignaturas = Grupo::detailed_materias( $grupo_id, $user->persona_id, true );
			}
		}

		$cant = count($mis_asignaturas);
		for ($i=0; $i < $cant; $i++) { 

			$consulta 			= 'SELECT * FROM ws_actividades a WHERE a.asignatura_id=? and a.deleted_at is null and a.periodo_id=?';
			$actividades 		= DB::select($consulta, [ $mis_asignaturas[$i]->asignatura_id, $user->periodo_id ]);
			$mis_asignaturas[$i]->actividades = $actividades;
		
		}

		$cant = count($otras_asignaturas);
		for ($i=0; $i < $cant; $i++) { 

			$consulta 			= 'SELECT * FROM ws_actividades a WHERE a.asignatura_id=? and a.deleted_at is null and a.periodo_id=?';
			$actividades 		= DB::select($consulta, [ $otras_asignaturas[$i]->asignatura_id, $user->periodo_id ]);
			$otras_asignaturas[$i]->actividades = $actividades;
		
		}

		$datos['mis_asignaturas'] 	= $mis_asignaturas;
		$datos['otras_asignaturas'] = $otras_asignaturas;
		$datos['grupo_id'] 			= $grupo_id;
		



		return $datos;

	}

	public function putEdicion()
	{
		$user 			= User::fromToken();
		$actividad_id 	= Request::input('actividad_id');
		$datos 			= [];

		$consulta 			= 'SELECT * FROM ws_actividades a WHERE a.id=? and a.deleted_at is null';
		$actividad 			= DB::select($consulta, [ Request::input('actividad_id') ])[0];
		
		$consulta 			= 'SELECT * FROM (
									SELECT p.id, TRUE as is_preg, p.actividad_id, p.enunciado, p.orden, p.added_by, p.created_at, p.updated_at, NULL as is_cuadricula,
										p.ayuda, p.tipo_pregunta, p.puntos, p.duracion, p.aleatorias, p.texto_arriba, p.texto_abajo 
									FROM ws_preguntas p 
									WHERE p.actividad_id=:actividad_id1 and p.deleted_at is null
								union
									SELECT c.id, TRUE as is_preg, c.actividad_id, c.enunciado, c.orden, c.added_by, c.created_at, c.updated_at, c.is_cuadricula,
										NULL as ayuda, NULL as tipo_pregunta, NULL as puntos, NULL as duracion, NULL as aleatorias, NULL as texto_arriba, NULL as texto_abajo 
									FROM ws_contenidos_preg c 
									WHERE c.actividad_id=:actividad_id2 and c.deleted_at is null
								
								)p order by orden DESC, created_at';
		

		$preguntas 			= DB::select($consulta, [ 
										':actividad_id1' => $actividad_id,
										':actividad_id2' => $actividad_id, 
									]);

		$cant = count($preguntas);

		for ($i=0; $i < $cant; $i++) { 
			
			if ($preguntas[$i]->is_preg) {
				
				$consulta = 'SELECT o.id, o.pregunta_id, o.definicion, o.image_id, o.orden, o.is_correct, o.created_at, o.updated_at 
						FROM ws_opciones o
						where o.pregunta_id=:pregunta_id';

				$opciones = DB::select($consulta, [':pregunta_id' => $preguntas[$i]->id] );
				$preguntas[$i]->opciones = $opciones;

			}else{

				$consulta = 'SELECT p.id, TRUE as is_preg, p.actividad_id, p.enunciado, p.orden, p.added_by, p.created_at, p.updated_at, NULL as is_cuadricula,
									p.ayuda, p.tipo_pregunta, p.puntos, p.duracion, p.aleatorias, p.texto_arriba, p.texto_abajo 
								FROM ws_preguntas p 
								WHERE p.actividad_id=:actividad_id1 and p.deleted_at is null';

				$opciones = DB::select($consulta, [':pregunta_id' => $preguntas[$i]->id] );
				$preguntas[$i]->opciones = $opciones;


			}
		}

		
		$actividad->preguntas = $preguntas;

		$datos['actividad'] = $actividad;
		
		return $datos;
	}

	public function putGuardar()
	{
		$user 	= User::fromToken();

		$act = WsActividad::findOrFail(Request::input('id'));

		$act->descripcion	=	Request::input('descripcion');
		$act->compartida	=	Request::input('compartida');
		$act->can_upload	=	Request::input('can_upload');
		$act->tipo			=	Request::input('tipo');
		$act->in_action		=	Request::input('in_action');
		$act->duracion_preg	=	Request::input('duracion_preg');
		$act->duracion_exam	=	Request::input('duracion_exam');
		$act->oportunidades	=	Request::input('oportunidades');
		$act->one_by_one	=	Request::input('one_by_one');
		$act->tipo_calificacion	=	Request::input('tipo_calificacion'); // 'Sin puntaje', 'Por promedio', 'Por puntos' 
		$act->contenido		=	Request::input('contenido');
		$act->inicia_at		=	Request::input('inicia_at_str');
		$act->finaliza_at	=	Request::input('finaliza_at_str');
		$act->save();

		return $act;
	}

	public function deleteDestroy($id)
	{
		$act = WsActividad::findOrFail($id);
		$act->delete();

		return $act;
	}

}