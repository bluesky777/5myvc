<?php namespace App\Http\Controllers;

use Request;
use DB;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Nota;
use App\Models\Unidad;
use App\Models\Subunidad;
use App\Models\Asignatura;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\Bitacora;
use App\Models\FraseAsignatura;


class NotasController extends Controller {


	public function getIndex()
	{
		return Nota::all();
	}


	public function getDetailed($asignatura_id)
	{
		$user = User::fromToken();

		$resultado = [];

		$unidades = Unidad::where('asignatura_id', $asignatura_id)
					->where('periodo_id', $user->periodo_id)->orderBy('orden')->orderBy('id')->get();

		$asignatura = (object)Asignatura::detallada($asignatura_id, $user->year_id);
		
		foreach ($unidades as $unidad) {
			$subunidades = Subunidad::where('unidad_id', $unidad->id)->orderBy('orden')->orderBy('id')->get();

			foreach ($subunidades as $subunidad) {

				$notas = Nota::where('subunidad_id', $subunidad->id)->get();

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
			$frases = FraseAsignatura::deAlumno($asignatura->asignatura_id, $alumno->alumno_id, $user->periodo_id);
			$alumno->frases = $frases;

			// Ausencias
			$cons_aus = "SELECT  a.id, a.asignatura_id, a.alumno_id, a.periodo_id, a.cantidad_ausencia, a.cantidad_tardanza, a.entrada, a.fecha_hora, a.uploaded, a.created_by FROM ausencias a
						inner join periodos p on p.id=a.periodo_id and p.year_id=:year_id
						WHERE a.tipo='ausencia' and a.asignatura_id=:asignatura_id and a.alumno_id=:alumno_id and a.deleted_at is null;";
			$ausencias = DB::select($cons_aus, [":year_id" => $user->year_id, ':asignatura_id' => $asignatura->asignatura_id, ':alumno_id' => $alumno->alumno_id ]);
			$alumno->ausencias 			= $ausencias;
			$alumno->ausencias_count 	= count($ausencias);

			// Tardanzas
			$cons_tar = "SELECT  a.id, a.asignatura_id, a.alumno_id, a.periodo_id, a.cantidad_ausencia, a.cantidad_tardanza, a.entrada, a.fecha_hora, a.uploaded, a.created_by FROM ausencias a
						inner join periodos p on p.id=a.periodo_id and p.year_id=:year_id
						WHERE a.tipo='tardanza' and a.asignatura_id=:asignatura_id and a.alumno_id=:alumno_id and a.deleted_at is null;";
			$tardanzas = DB::select($cons_tar, [":year_id" => $user->year_id, ':asignatura_id' => $asignatura->asignatura_id, ':alumno_id' => $alumno->alumno_id ]);
			$alumno->tardanzas 			= $tardanzas;
			$alumno->tardanzas_count 	= count($tardanzas);

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


		if ($user->alumnos_can_see_notas==false) {
			$usuario = User::find($user->user_id);
			if ($usuario->hasRole('alumno') || $usuario->hasRole('acudiente')) {
				return 'Sistema bloqueado. No puedes ver las notas';				
			}
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
		$user 	= User::fromToken();
		$nota 	= Nota::find($nota_id);
		return $nota;
	}


	public function postStore()
	{
		return 'No se puede agregar nota';
	}



	public function putUpdate($id)
	{
		$user 	= User::fromToken();
		$now 	= Carbon::now('America/Bogota');

		try {

			$consulta 	= 'SELECT n.*, h.id as history_id FROM notas n, 
								(select * from historiales where user_id=? and deleted_at is null order by id desc limit 1 ) h 
							WHERE n.id=? and n.deleted_at is null ';

			$nota 		= DB::select($consulta, [$user->user_id, $id])[0];

			$bit_by 	= $user->user_id;
			$bit_hist 	= $nota->history_id;
			$bit_old 	= $nota->nota; 				// Guardo la nota antigua
			$bit_new 	= Request::input('nota'); 	// Guardo la nota nueva
			$bit_per 	= $user->periodo_id;

			$nota->nota 		= $bit_new;
			$nota->updated_at 	= $now;
			$nota->updated_by 	= $user->user_id;

			$consulta 	= 'UPDATE notas SET nota=?, updated_by=?, updated_at=? WHERE id=?';
			DB::update($consulta, [$bit_new, $user->user_id, $now, $id]);

			$consulta 	= 'INSERT INTO bitacoras (created_by, historial_id, affected_user_id, affected_person_type, affected_element_type, affected_element_id, affected_element_new_value_int, affected_element_old_value_int, created_at) 
						VALUES (?, ?, ?, "Al", "Nota", ?, ?, ?, ?)';

			DB::insert($consulta, [$bit_by, $bit_hist, $nota->alumno_id, $id, $bit_new, $bit_old, $now]);

		} catch (Exception $e) {
			return abort(400, 'No se pudo guardar la nota');
		}
		

		return (array)$nota;
	}


	public function deleteDestroy($id)
	{
		$nota = Nota::findOrFail($id);
		$nota->delete();

		return $nota;
	}

}