<?php namespace App\Http\Controllers\Actividades;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\WsActividad;
use App\Models\WsPregunta;
use App\Models\WsOpcion;


class PreguntasController extends Controller {


	public function postCrear()
	{
		$user = User::fromToken();

		$preg 					= new WsPregunta;
		$preg->actividad_id 	= Request::input('actividad_id');
		$preg->tipo_pregunta 	= 'Test'; // Test, Multiple, Texto, Lista, Ordenar, Cuadrícula
		$preg->orden 			= Request::input('orden');
		$preg->added_by 		= $user->user_id;
		$preg->save();


		$opcion 				= new WsOpcion();
		$opcion->definicion 	= 'Opcion 1';
		$opcion->pregunta_id 	= $preg->id;
		$opcion->orden 			= 0;
		$opcion->is_correct 	= true;
		$opcion->save();

		$preg->opciones = [$opcion];

		return $preg;
	}


	public function putEdicion()
	{
		$user 	= User::fromToken();
		
		$datos 	= [];

		$consulta 	= 'SELECT p.id, TRUE as is_preg, p.actividad_id, p.enunciado, p.orden, p.added_by, p.created_at, p.updated_at, NULL as is_cuadricula,
							p.ayuda, p.tipo_pregunta, p.puntos, p.duracion, p.aleatorias, p.texto_arriba, p.texto_abajo 
						FROM ws_preguntas p 
						WHERE p.id=? and p.deleted_at is null';
		$Pregunta 	= DB::select($consulta, [ Request::input('pregunta_id') ])[0];


		$consulta = 'SELECT o.id, o.pregunta_id, o.definicion, o.image_id, o.orden, o.is_correct, o.created_at, o.updated_at 
				FROM ws_opciones o
				where o.pregunta_id=:pregunta_id';

		$opciones = DB::select($consulta, [ ':pregunta_id' => Request::input('pregunta_id') ] );
		$Pregunta->opciones = $opciones;
		
		$datos['pregunta'] = $Pregunta;
		
		return $datos;
	}


	public function putGuardar()
	{
		$user 	= User::fromToken();
		
		$preg 					= WsPregunta::find(Request::input('id'));
		$preg->enunciado 		= Request::input('enunciado');
		$preg->ayuda 			= Request::input('ayuda');
		$preg->puntos 			= Request::input('puntos');
		$preg->duracion 		= Request::input('duracion');
		$preg->aleatorias 		= Request::input('aleatorias');
		$preg->texto_arriba 	= Request::input('texto_arriba');
		$preg->texto_abajo 		= Request::input('texto_abajo');
		$preg->save();

		return $preg;
	}



	public function deleteDestroy($id)
	{
		$user = User::fromToken();

		$preg = WsPregunta::findOrFail($id);
		$preg->delete();

		return $preg;
	}

}