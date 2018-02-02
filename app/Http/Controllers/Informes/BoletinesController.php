<?php namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;

use Request;
use DB;
use Hash;

use App\Models\User;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Year;
use App\Models\Nota;
use App\Models\Alumno;
use App\Models\Role;
use App\Models\Matricula;
use App\Models\Unidad;
use App\Models\Subunidad;
use App\Models\Ausencia;
use App\Models\FraseAsignatura;
use App\Models\Asignatura;
use App\Models\NotaComportamiento;
use App\Models\DefinicionComportamiento;
use App\Models\ImageModel;

use Carbon\Carbon;


class BoletinesController extends Controller {

	public function putDetailedNotasGroup($grupo_id)
	{
		$user = User::fromToken();

		$periodo_a_calcular = Request::input('periodo_a_calcular', 10);

		$boletines = $this->detailedNotasGrupo($grupo_id, $user, '', $periodo_a_calcular);

		//$grupo->alumnos = $alumnos;
		//$grupo->asignaturas = $asignaturas;
		//return (array)$grupo;

		return $boletines;


	}

	public function getDetailedNotasYear($grupo_id, $periodo_a_calcular=10)
	{
		$user = User::fromToken();

		$alumnos_response = [];

		$grupo			= Grupo::datos($grupo_id);
		$year			= Year::datos($user->year_id);
		$alumnos		= Grupo::alumnos($grupo_id);

		//return Nota::alumnoAsignaturasPeriodosDetailed($alumno->alumno_id, $user->year_id, $periodos_a_calcular, $user->numero_periodo); // borrar

		foreach ($alumnos as $keyAlum => $alumno) {
			$alumno = Nota::alumnoAsignaturasPeriodosDetailed($alumno->alumno_id, $user->year_id, $periodo_a_calcular, $user->numero_periodo);
			array_push($alumnos_response, $alumno);
		}



		return array($grupo, $year, $alumnos_response);


	}


	public function putDetailedNotas($grupo_id)
	{
		$user = User::fromToken();

		$periodo_a_calcular 	= Request::input('periodo_a_calcular', 10);
		$requested_alumnos 		= Request::input('requested_alumnos', '');

		$boletines = $this->detailedNotasGrupo($grupo_id, $user, $requested_alumnos, $periodo_a_calcular);

		//$grupo->alumnos = $alumnos;
		//$grupo->asignaturas = $asignaturas;
		//return (array)$grupo;

		return $boletines;


	}

	public function detailedNotasGrupo($grupo_id, $user, $requested_alumnos='', $periodo_a_calcular=10)
	{
		
		$grupo			= Grupo::datos($grupo_id);
		$year			= Year::datos($user->year_id);
		$alumnos		= Grupo::alumnos($grupo_id, $requested_alumnos);

		$year->periodos = Periodo::hastaPeriodoN($user->year_id, $periodo_a_calcular);
		
		//$year->periodos = Periodo::hastaPeriodo($user->year_id, $periodo_a_calcular, $periodo_usuario);

		$grupo->cantidad_alumnos = count($alumnos);

		$response_alumnos = [];
		

		foreach ($alumnos as $alumno) {

			// Todas las materias con sus unidades y subunides
			$this->allNotasAlumno($alumno, $grupo_id, $user->periodo_id, true);

			$alumno->userData = Alumno::userData($alumno->alumno_id);

			$asignaturas_perdidas = $this->asignaturasPerdidasDeAlumno($alumno, $grupo_id, $user->year_id, $periodo_a_calcular);

			if (count($asignaturas_perdidas) > 0) {
				
				$alumno->asignaturas_perdidas = $asignaturas_perdidas;
				$alumno->notas_perdidas_year = 0;
				$alumno->periodos_con_perdidas = Periodo::hastaPeriodoN($user->year_id, $periodo_a_calcular);

				foreach ($alumno->periodos_con_perdidas as $keyPerA => $periodoAlone) {

					$periodoAlone->cant_perdidas = 0;
					
					foreach ($alumno->asignaturas_perdidas as $keyAsig => $asignatura_perdida) {

						foreach ($asignatura_perdida->periodos as $keyPer => $periodo) {

							if ($periodoAlone->periodo_id == $periodo->periodo_id) {
								if ($periodo->id == $periodoAlone->id) {
									$periodoAlone->cant_perdidas += $periodo->cantNotasPerdidas;
								}
								
							}
						}
					}

					$alumno->notas_perdidas_year += $periodoAlone->cant_perdidas;
					
				}
			}
		}


		foreach ($alumnos as $alumno) {
			
			$alumno->puesto = Nota::puestoAlumno($alumno->promedio, $alumnos);
			
			if ($requested_alumnos == '') {

				array_push($response_alumnos, $alumno);

			}else{

				foreach ($requested_alumnos as $req_alumno) {
					
					if ($req_alumno['alumno_id'] == $alumno->alumno_id) {
						array_push($response_alumnos, $alumno);
					}
				}
			}
			

		}

		return array($grupo, $year, $response_alumnos);
	}

	public function allNotasAlumno(&$alumno, $grupo_id, $periodo_id, $comport_and_frases=false)
	{


		$asignaturas		= Grupo::detailed_materias($grupo_id);
		$ausencias_total	= Ausencia::totalDeAlumno($alumno->alumno_id, $periodo_id);

		$alumno->ausencias_total = $ausencias_total;

		foreach ($asignaturas as $asignatura) {
			$asignatura->unidades = Unidad::deAsignatura($asignatura->asignatura_id, $periodo_id);

			foreach ($asignatura->unidades as $unidad) {
				$unidad->subunidades = Subunidad::deUnidad($unidad->unidad_id);
			}
		}

		$alumno->asignaturas = $asignaturas;

		$sumatoria_asignaturas = 0;

		foreach ($alumno->asignaturas as $asignatura) {

			if ($comport_and_frases) {
				$asignatura->ausencias	= Ausencia::deAlumno($asignatura->asignatura_id, $alumno->alumno_id, $periodo_id);
				$asignatura->frases		= FraseAsignatura::deAlumno($asignatura->asignatura_id, $alumno->alumno_id, $periodo_id);
			}

			Asignatura::calculoAlumnoNotas($asignatura, $alumno->alumno_id);

			$sumatoria_asignaturas += $asignatura->nota_asignatura; // Para sacar promedio del periodo


			// SUMAR AUSENCIAS Y TARDANZAS
			if ($comport_and_frases) {
				$cantAus = 0;
				$cantTar = 0;
				foreach ($asignatura->ausencias as $ausencia) {
					if ($ausencia->tipo == "tardanza") {
						$cantTar += (int)$ausencia->cantidad_tardanza;
					}elseif ($ausencia->tipo == "ausencia") {
						$cantAus += (int)$ausencia->cantidad_ausencia;
					}
					
				}

				$asignatura->total_ausencias = $cantAus;
				$asignatura->total_tardanzas = $cantTar;
			}

		}

		if (count($alumno->asignaturas) == 0) {
			$alumno->promedio = 0;
		} else {
			$alumno->promedio = $sumatoria_asignaturas / count($alumno->asignaturas);
		}
			



		// COMPORTAMIENTO Y SUS FRASES
		if ($comport_and_frases) {
			/* eliminar:
			$comportamiento = NotaComportamiento::where('alumno_id', $alumno->alumno_id)
												->where('periodo_id', $periodo_id)
												->first();
			*/
			$comportamiento = NotaComportamiento::nota_comportamiento($alumno->alumno_id, $periodo_id);

			$alumno->comportamiento = $comportamiento;
			$definiciones = [];

			if ($comportamiento) {
				$definiciones = DefinicionComportamiento::frases($comportamiento->id);
				$alumno->comportamiento->definiciones = $definiciones;
			}


		}
		


		return $alumno;
	}


	public function asignaturasPerdidasDeAlumno($alumno, $grupo_id, $year_id, $periodo_a_calcular)
	{
		$asignaturas	= Grupo::detailed_materias($grupo_id);


		foreach ($asignaturas as $keyAsig => $asignatura) {
			
			$periodos = Periodo::hastaPeriodoN($year_id, $periodo_a_calcular);

			$asignatura->cantTotal = 0;

			foreach ($periodos as $keyPer => $periodo) {

				$periodo->cantNotasPerdidas = 0;
				$periodo->unidades = Unidad::deAsignatura($asignatura->asignatura_id, $periodo->id);


				foreach ($periodo->unidades as $keyUni => $unidad) {
					
					$subunidades = Subunidad::perdidasDeUnidad($unidad->unidad_id, $alumno->alumno_id);
					
					if (count($subunidades) > 0) {
						$unidad->subunidades = $subunidades;
						$periodo->cantNotasPerdidas += count($subunidades);
					}else{
						$uniTemp = $periodo->unidades;
						unset($uniTemp[$keyUni]);
						$periodo->unidades = $uniTemp;
					}
				}
				#$periodo->unidades = $unidades;

				$asignatura->cantTotal += $periodo->cantNotasPerdidas;
				
				if (count($periodo->unidades) > 0) {
					#$periodo->unidades = $unidades;
				}else{
					unset($periodos[$keyPer]);
				}
				
				
			}

			if (count($periodos) > 0) {
				$asignatura->periodos = $periodos;
			}else{
				unset($asignaturas[$keyAsig]);
			}

			$hasPeriodosConPerdidas = false;

			foreach ($periodos as $keyPer => $periodo) {
				if (count($periodo->unidades) > 0) {
					$hasPeriodosConPerdidas = true;
				}
			}

			if (!$hasPeriodosConPerdidas) {
				unset($asignaturas[$keyAsig]);
			}

		}

		return $asignaturas;

	}

	public function periodosPerdidosDeAlumno($alumno, $grupo_id, $year_id, $periodos)
	{
		//$periodos = Periodo::where('year_id', '=', $year_id)->get();

		foreach ($periodos as $key => $periodo) {
			$periodo->asignaturas = $this->asignaturasPerdidasDeAlumnoPorPeriodo($alumno->alumno_id, $grupo_id, $periodo->id);

			if (count($periodo->asignaturas)==0) {
				unset($periodos[$key]);
			}
		}
	}

	public function asignaturasPerdidasDeAlumnoPorPeriodo($alumno_id, $grupo_id, $periodo_id)
	{


		$asignaturas	= Grupo::detailed_materias($grupo_id);

		foreach ($asignaturas as $keyAsig => $asignatura) {

			$asignatura->unidades = Unidad::deAsignatura($asignatura->asignatura_id, $periodo_id);

			foreach ($asignatura->unidades as $keyUni => $unidad) {
				$unidad->subunidades = Subunidad::perdidasDeUnidad($unidad->unidad_id, $alumno_id);

				if (count($unidad->subunidades) == 0) {
					unset($asignatura->unidades[$keyUni]);
				}
			}
			if (count($asignatura->unidades) == 0) {
				unset($asignaturas[$keyAsig]);
			}
		}


		return $asignaturas;
	}


	public function deleteDestroy($id)
	{
		$alumno = Alumno::find($id);
		//Alumno::destroy($id);
		//$alumno->restore();
		//$queries = DB::getQueryLog();
		//$last_query = end($queries);
		//return $last_query;

		if ($alumno) {
			$alumno->delete();
		}else{
			return abort(400, 'Alumno no existe o está en Papelera.');
		}
		return $alumno;
	
	}	

	public function deleteForcedelete($id)
	{
		$alumno = Alumno::onlyTrashed()->findOrFail($id);
		
		if ($alumno) {
			$alumno->forceDelete();
		}else{
			return abort(400, 'Alumno no encontrado en la Papelera.');
		}
		return $alumno;
	
	}

	public function putRestore($id)
	{
		$alumno = Alumno::onlyTrashed()->findOrFail($id);

		if ($alumno) {
			$alumno->restore();
		}else{
			return abort(400, 'Alumno no encontrado en la Papelera.');
		}
		return $alumno;
	}


	public function getTrashed()
	{
		$user = User::fromToken();
		$previous_year = $user->year - 1;
		$id_previous_year = 0;
		$previous_year = Year::where('year', '=', $previous_year)->first();


		$consulta = 'SELECT m2.matricula_id, a.id as alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
				a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
				m2.year_id, m2.grupo_id, m2.nombregrupo, m2.abrevgrupo, IFNULL(m2.actual, -1) as currentyear,
				u.username, u.is_superuser, u.is_active
			FROM alumnos a left join 
				(select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 0 as actual
				from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:id_previous_year
				and m.alumno_id NOT IN 
					(select m.alumno_id
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:year_id)
					union
					select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 1 AS actual
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:year2_id
				)m2 on a.id=m2.alumno_id
			left join users u on u.id=a.user_id where a.deleted_at is not null';

		return DB::select($consulta, [
						':id_previous_year'	=>$id_previous_year, 
						':year_id'			=>$user->year_id,
						':year2_id'			=>$user->year_id
			]);
	}

}