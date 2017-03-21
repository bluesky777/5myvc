<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Year;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Asignatura;
use App\Models\Subunidad;
use App\Models\Unidad;
use App\Models\Profesor;


class PlanillasController extends Controller {

	public function getShowGrupo($grupo_id)
	{
		$user = User::fromToken();

		$year 			= Year::datos_basicos($user->year_id);
		$asignaturas 	= Grupo::detailed_materias($grupo_id);
		$periodos 		= Periodo::delYear($user->year_id);
		

		$year->periodos = $periodos;
		
		$grupo			= Grupo::datos($grupo_id);

		foreach ($asignaturas as $keyMat => $asignatura) {
			
			$alumnos	= Grupo::alumnos($grupo_id);
			$asignatura->nombre_grupo = $grupo->nombre_grupo;

			$asignatura->periodosProm = Periodo::delYear($user->year_id);

			// A cada alumno le daremos los periodos y la definitiva de cada periodo
			foreach ($alumnos as $keyAl => $alumno) {

				$periodosTemp = Periodo::delYear($user->year_id);

				foreach ($periodosTemp as $keyPer => $periodo) {

					// Unidades y subunidades de la asignatura en el periodo
					$asignaturaTemp = Asignatura::find($asignatura->asignatura_id);
					$asignaturaTemp->unidades = Unidad::deAsignatura($asignaturaTemp->id, $periodo->id);

					foreach ($asignaturaTemp->unidades as $unidad) {
						$unidad->subunidades = Subunidad::deUnidad($unidad->unidad_id);
					}

					// Traemos las notas de esta asignatura segun las unidades y subunidades calculadas arriba
					Asignatura::calculoAlumnoNotas($asignaturaTemp, $alumno->alumno_id);
					$periodo->nota_asignatura = $asignaturaTemp->nota_asignatura;
					unset($asignaturaTemp);
				}

				$alumno->periodos = $periodosTemp;
				unset($periodosTemp);





				foreach ($asignatura->periodosProm as $keyPer => $periodo) {
					if (! isset($periodo->sumatoria)) {
						$periodo->sumatoria = 0;
					}

					foreach ($alumno->periodos as $keyPerAl => $periodo_alum) {

						if ($periodo_alum->id == $periodo->id) {
							$periodo->sumatoria += $periodo_alum->nota_asignatura;
						}
					}
				}


			}

			$asignatura->alumnos = $alumnos;

		}

		return array($year, $asignaturas);
	}



	public function getShowProfesor($profesor_id)
	{
		$user = User::fromToken();

		$year 			= Year::datos_basicos($user->year_id);
		$asignaturas 	= Profesor::asignaturas($user->year_id, $profesor_id);
		$periodos 		= Periodo::where('year_id', '=', $user->year_id)->get();

		$year->periodos 	= $periodos;
		$profesor = Profesor::detallado($profesor_id);
		
		foreach ($asignaturas as $keyAsig => $asignatura) {
			
			$alumnos	= Grupo::alumnos($asignatura->grupo_id);

			$asignatura->nombres_profesor 		= $profesor->nombres_profesor;
			$asignatura->apellidos_profesor 	= $profesor->apellidos_profesor;
			$asignatura->foto_nombre 			= $profesor->foto_nombre;
			$asignatura->foto_id 				= $profesor->foto_id;
			$asignatura->sexo 					= $profesor->sexo;


			$asignatura->periodosProm = Periodo::where('year_id', $user->year_id)->get();

			// A cada alumno le daremos los periodos y la definitiva de cada periodo
			foreach ($alumnos as $keyAl => $alumno) {

				$periodosTemp = Periodo::where('year_id', $user->year_id)->get();

				foreach ($periodosTemp as $keyPer => $periodo) {

					// Unidades y subunidades de la asignatura en el periodo
					$asignaturaTemp = Asignatura::find($asignatura->asignatura_id);
					$asignaturaTemp->unidades = Unidad::deAsignatura($asignaturaTemp->id, $periodo->id);

					foreach ($asignaturaTemp->unidades as $unidad) {
						$unidad->subunidades = Subunidad::deUnidad($unidad->unidad_id);
					}

					// Traemos las notas de esta asignatura segun las unidades y subunidades calculadas arriba
					Asignatura::calculoAlumnoNotas($asignaturaTemp, $alumno->alumno_id);
					$periodo->nota_asignatura = $asignaturaTemp->nota_asignatura;
					unset($asignaturaTemp);
				}

				$alumno->periodos = $periodosTemp;
				unset($periodosTemp);





				foreach ($asignatura->periodosProm as $keyPer => $periodo) {
					if (!$periodo->sumatoria) {
						$periodo->sumatoria = 0;
					}

					foreach ($alumno->periodos as $keyPerAl => $periodo_alum) {

						if ($periodo_alum->id == $periodo->id) {
							$periodo->sumatoria += $periodo_alum->nota_asignatura;
						}
					}
				}


			}

			$asignatura->alumnos = $alumnos;

		}

		return array($year, $asignaturas);
	}



}