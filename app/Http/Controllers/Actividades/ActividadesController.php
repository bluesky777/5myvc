<?php namespace App\Http\Controllers\Actividades;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\Actividad;
use App\Models\Grupo;


class ActividadesController extends Controller {


	public function postCrear()
	{
		$user = User::fromToken();

		$acti 					= new Actividad;
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

		$consulta = 'SELECT * FROM grupos g WHERE g.year_id=? and g.deleted_at is null';
		$grupos = DB::select($consulta, [$user->year_id]);
		$datos['grupos'] = $grupos;

		if ($user->is_superuser) {
			$otras_asignaturas = Grupo::detailed_materias( Request::input('grupo_id') );
		}

		if ($user->tipo == 'Profesor') {

			$mis_asignaturas = Grupo::detailed_materias( Request::input('grupo_id'), $user->persona_id );
			$otras_asignaturas = Grupo::detailed_materias( Request::input('grupo_id'), $user->persona_id, true );
		}

		$cant = count($mis_asignaturas);
		for ($i=0; $i < $cant; $i++) { 

			$consulta 			= 'SELECT * FROM ws_actividades a WHERE a.asignatura_id=? and a.deleted_at is null';
			$actividades 		= DB::select($consulta, [ $mis_asignaturas[$i]->asignatura_id ]);
			$mis_asignaturas[$i]->actividades = $actividades;
		
		}

		$cant = count($otras_asignaturas);
		for ($i=0; $i < $cant; $i++) { 

			$consulta 			= 'SELECT * FROM ws_actividades a WHERE a.asignatura_id=? and a.deleted_at is null';
			$actividades 		= DB::select($consulta, [ $otras_asignaturas[$i]->asignatura_id ]);
			$otras_asignaturas[$i]->actividades = $actividades;
		
		}

		$datos['mis_asignaturas'] 	= $mis_asignaturas;
		$datos['otras_asignaturas'] = $otras_asignaturas;
		



		return $datos;

	}

	public function putEdicion()
	{
		$user 	= User::fromToken();
		
		$datos 	= [];

		$consulta 			= 'SELECT * FROM ws_actividades a WHERE a.id=? ';
		$actividad 			= DB::select($consulta, [ Request::input('actividad_id') ]);
		
		$datos['actividad'] = $actividad;
		
		return $datos;
	}

	public function putUpdate($id)
	{
		$act = Actividad::findOrFail($id);

		$act->definicion	=	Request::input('definicion');
		$act->save();

	}

	public function deleteDestroy($id)
	{
		$act = Actividad::findOrFail($id);
		$act->delete();

		return $act;
	}

}