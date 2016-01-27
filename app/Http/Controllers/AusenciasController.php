<?php namespace App\Http\Controllers;

class AusenciasController extends Controller {

	public function getIndex()
	{
		//
	}

	public function getDetailed($asignatura_id)
	{
		$user = User::fromToken();

		$asignatura = (object)Asignatura::detallada($asignatura_id);
		
		$alumnos = Grupo::alumnos($asignatura->grupo_id);
		
		foreach ($alumnos as $alumno) {

			$userData = Alumno::userData($alumno->alumno_id);
			$alumno->userData = $userData;

			$consulta = 'SELECT * FROM ausencias a WHERE a.asignatura_id = ? and a.periodo_id = ? and a.alumno_id=?';

			$ausencias = DB::select(DB::raw($consulta), array($asignatura_id, $user->periodo_id, $alumno->alumno_id));

			foreach ($ausencias as $ausencia) {
				$ausencia->mes = date('n', strtotime($ausencia->fecha_hora)) - 1;
				$ausencia->dia = (integer)(date('j', strtotime($ausencia->fecha_hora))) ;
			}
			
			$alumno->ausencias = $ausencias;
		}

		// No cambiar el orden!
		$resultado = [];
		array_push($resultado, $asignatura);
		array_push($resultado, $alumnos);

		return $resultado;
	}

	public function postStore()
	{
		$user = User::fromToken();

		$aus = new Ausencia;
		$aus->alumno_id 		= Input::get('alumno_id');
		$aus->asignatura_id 	= Input::get('asignatura_id');
		$aus->periodo_id		= $user->periodo_id;
		$aus->cantidad_ausencia	= Input::get('cantidad_ausencia', null);
		$aus->cantidad_tardanza	= Input::get('cantidad_tardanza', null);
		$aus->fecha_hora		= Input::get('fecha_hora', null);
		$aus->created_by		= $user->user_id;

		$aus->save();
		return $aus;
	}

	public function getShow($id)
	{
		//
	}

	public function putUpdate($id)
	{
		//
	}

	public function deleteDestroy($id)
	{
		$user = User::fromToken();

		$aus = Ausencia::findOrFail($id);
		$aus->delete();
		return $aus;
	}

}