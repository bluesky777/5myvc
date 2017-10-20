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
use App\Models\Alumno;


class PlanillasController extends Controller {


	private $periodos = [];


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
		$periodos 		= Periodo::where('year_id', $user->year_id)->get();

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


	public function getVerAusencias()
	{
		$user 		= User::fromToken();
		$this->periodos 	= Periodo::where('year_id', $user->year_id)->get();


		$consulta = 'SELECT g.id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
			p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
			g.created_at, g.updated_at, gra.nombre as nombre_grado 
			from grupos g
			inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
			left join profesores p on p.id=g.titular_id
			where g.deleted_at is null
			order by g.orden';

		$grupos = DB::select($consulta, [':year_id' => $user->year_id]);

		$cant = count($grupos);
		for ($i=0; $i < $cant; $i++) { 

			$alumnos = Grupo::alumnos($grupos[$i]->id);

			$grupos[$i]->periodos 	= $this->periodos; // No debería repetir tanto pero bueno
			$grupos[$i]->alumnos 	= $alumnos;



			$cant_alum		= count($alumnos);
			for ($k=0; $k < $cant_alum; $k++) { 
				$alumnos[$k]->periodos = Periodo::where('year_id', $user->year_id)->get();

				$total_aus_alum = 0;

				$cant_pers = count($alumnos[$k]->periodos);

				for ($j=0; $j < $cant_pers; $j++) { 
					

					$consulta = 'SELECT  a.id, a.asignatura_id, a.alumno_id, a.periodo_id, a.cantidad_ausencia, a.cantidad_tardanza, a.entrada, a.tipo, a.fecha_hora, a.uploaded, a.created_by,
							u.username, u2.username as username_updater, a.updated_by, a.created_at
						FROM ausencias a
						inner join periodos p on p.id=a.periodo_id and p.id=:periodo_id
						inner join users u on u.id=a.created_by
						left join users u2 on u2.id=a.updated_by
						WHERE a.entrada=true and a.alumno_id=:alumno_id and a.deleted_at is null';

					$ausencias = DB::select($consulta, [':alumno_id' => $grupos[$i]->alumnos[$k]->alumno_id, ':periodo_id' => $grupos[$i]->alumnos[$k]->periodos[$j]->id ]);
					$grupos[$i]->alumnos[$k]->periodos[$j]->ausencias = $ausencias;
					$total_aus_alum = $total_aus_alum + count($ausencias);
				}

				$alumnos[$k]->total_aus_alum = $total_aus_alum;
			}
		}



		return $grupos;

	}



}