<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Nota;
use App\Models\Unidad;
use App\Models\Subunidad;
use App\Models\Asignatura;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\Bitacora;


class NotasController extends Controller {


	public function getIndex()
	{
		return Nota::all();
	}


	public function getDetailed($asignatura_id)
	{
		$user = User::fromToken();

		$resultado = [];

		$unidades = Unidad::where('asignatura_id', '=', $asignatura_id)
					->where('periodo_id', '=', $user->periodo_id)->orderBy('orden')->get();

		$asignatura = (object)Asignatura::detallada($asignatura_id);
		
		foreach ($unidades as $unidad) {
			$subunidades = Subunidad::where('unidad_id', '=', $unidad->id)->orderBy('orden')->get();

			foreach ($subunidades as $subunidad) {

				$notas = Nota::where('subunidad_id', '=', $subunidad->id)->get();

				if (count($notas) == 0) {

					$notasTemp = Nota::crearNotas($asignatura->grupo_id, $subunidad);

					$subunidad->notas = $notasTemp;
				}else{

					$notas = Nota::verificarCrearNotas($asignatura->grupo_id, $subunidad);
					$subunidad->notas = $notas;

				}
			}

			$unidad->subunidades = $subunidades;
		}

		


		$alumnos = Grupo::alumnos($asignatura->grupo_id);

		foreach ($alumnos as $alumno) {

			$userData = Alumno::userData($alumno->alumno_id);
			$alumno->userData = $userData;

		}

		// No cambiar el orden!
		array_push($resultado, $asignatura);
		array_push($resultado, $alumnos);
		array_push($resultado, $unidades);

		return $resultado;
	}

	public function getAlumno($alumno_id='')
	{
		$user = User::fromToken();

		$usuario = User::find($user->user_id);

		if ($user->alumnos_can_see_notas==false && ( $usuario->hasRole('alumno') || $usuario->hasRole('acudiente') ) ) {
			return 'Sistema bloqueado. No puedes ver las notas';
		}

		if ($alumno_id=='') {
			if ($user->tipo == 'Alumno') {
				$alumno_id = $user->persona_id;
			}else{
				return abort(400, 'No hay id de alumno');
			}
		}

		$profesor_id = '';

		if ($user->tipo == 'Profesor') {
			$profesor_id = $user->persona_id;
		}

		$datos = Nota::alumnoPeriodosDetailed($alumno_id, $user->year_id, $profesor_id);

		return [$datos];
	}



	public function getShow($nota_id)
	{
		$nota = Nota::find($nota_id);
		return $nota;
	}


	public function postStore()
	{
		return 'No se puede agregar nota';
	}



	public function putUpdate($id)
	{
		$user = User::fromToken();

		$bit = Bitacora::crear($user->user_id);

		try {
			$bit->periodo_id = $user->periodo_id;

			$nota = Nota::findOrFail($id);
			$bit->affected_element_old_value_int = $nota->nota; // Guardo la nota antigua

			$nota->nota = Request::input('nota');
			$bit->affected_element_new_value_int = $nota->nota; // Guardo la nota nueva
		
			$nota->updated_by = $user->user_id;

			$nota->save();
		} catch (Exception $e) {
			return abort(400, 'No se pudo guardar la nota');
		}
		

		$bit->saveUpdateNota($nota);

		return $nota;
	}


	public function deleteDestroy($id)
	{
		$nota = Nota::findOrFail($id);
		$nota->delete();

		return $nota;
	}

}