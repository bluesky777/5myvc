<?php namespace App\Http\Controllers\informes;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\Year;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Asignatura;
use App\Models\Subunidad;
use App\Models\Profesor;


class NotasPerdidasController extends Controller {

	public function putProfesorGrupos()
	{
		$user 	= User::fromToken();

		$periodo_a_calcular 	= (int)Request::input('periodo_a_calcular');
		$profesor_id 			= Request::input('profesor_id');
		//$periodos 				= Periodo::hastaPeriodo($user->year_id, $periodo_a_calcular, $user->numero_periodo);
		

		$consulta = 'SELECT g.id as grupo_id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
			p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
			g.created_at, g.updated_at, gra.nombre as nombre_grado 
			from grupos g
			inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
			left join profesores p on p.id=g.titular_id
			where g.deleted_at is null
			order by g.orden';

		$grupos_all 		= DB::select($consulta, [':year_id' => $user->year_id]);
		$cant_gr_all 		= count($grupos_all);

		for ($i=0; $i < $cant_gr_all; $i++) { 

			$consulta = 'SELECT a.id as asignatura_id, a.grupo_id, a.profesor_id, a.creditos, a.orden, m.materia, m.alias, p.nombres as profesor_nombres, p.apellidos as profesor_apellidos
				from asignaturas a
				inner join profesores p on p.id=a.profesor_id and p.id=:profesor_id
				inner join materias m on m.id=a.materia_id 
				where a.grupo_id=:grupo_id and a.deleted_at is null';

			$asign_all 		= DB::select($consulta, [':profesor_id' => $profesor_id, ':grupo_id' => $grupos_all[$i]->grupo_id ]);
			$cant_asig_all 	= count($asign_all);

			for ($j=0; $j < $cant_asig_all; $j++) { 


				$consulta = "SELECT a.id as alumno_id, a.nombres, a.apellidos, a.sexo, a.user_id, a.celular, a.email, a.foto_id, a.pazysalvo
					from alumnos a
					inner join matriculas m on m.alumno_id=a.id and (m.estado='MATR' or m.estado='ASIS') and m.grupo_id=:grupo_id and m.deleted_at is null
					inner join notas n on n.alumno_id=a.id and n.nota < :nota_minima_aceptada
					inner join subunidades s on s.id=n.subunidad_id and s.deleted_at is null 
					inner join unidades u on u.id=s.unidad_id and u.asignatura_id=:asignatura_id and u.deleted_at is null 
					inner join periodos p on p.id=u.periodo_id and p.numero<=:periodo and p.deleted_at is null 
					where a.deleted_at is null
					group by a.id order by a.apellidos";

				$alumn_all 		= DB::select($consulta, [ ':grupo_id' => $grupos_all[$i]->grupo_id, ':nota_minima_aceptada' => $user->nota_minima_aceptada, ':asignatura_id' => $asign_all[$j]->asignatura_id, 'periodo' => $periodo_a_calcular ]);
				$cant_alum		= count($alumn_all);
				
				for ($k=0; $k < $cant_alum; $k++) { 

					$consulta = 'SELECT a.id as alumno_id, a.nombres, a.apellidos, a.sexo, a.user_id, a.celular, a.email, a.foto_id, a.pazysalvo,
						n.nota, n.id as nota_id, n.subunidad_id, s.definicion as defin_subunidad, s.porcentaje as porc_subunidad, s.orden as orden_subunidad, s.created_at, 
						s.unidad_id, u.definicion as defin_unidad, u.porcentaje as porc_unidad, u.periodo_id, u.asignatura_id, u.orden as orden_unidad,
						p.numero as numero_periodo
						from alumnos a
						inner join notas n on n.alumno_id=a.id and n.nota < :nota_minima_aceptada 
						inner join subunidades s on s.id=n.subunidad_id and s.deleted_at is null 
						inner join unidades u on u.id=s.unidad_id and u.asignatura_id=:asignatura_id and u.deleted_at is null 
						inner join periodos p on p.id=u.periodo_id and p.numero<=:periodo and p.deleted_at is null 
						where a.id=:alumno_id and a.deleted_at is null';

					$notas 		= DB::select($consulta, [':nota_minima_aceptada' => $user->nota_minima_aceptada, ':asignatura_id' => $asign_all[$j]->asignatura_id, 
															'periodo' => $periodo_a_calcular, ':alumno_id' => $alumn_all[$k]->alumno_id ]);
					$alumn_all[$k]->notas = $notas;
				}

				$asign_all[$j]->alumnos = $alumn_all;

			}

			foreach ($asign_all as $keyAsig => $asignatura) {
				if (! count($asignatura->alumnos ) > 0) {
					unset($asign_all[$keyAsig]);
				}
			}

			$grupos_all[$i]->asignaturas = $asign_all;

		}

		$res = [];
		foreach ($grupos_all as $keyGr => $grupo) {
			if (! count($grupo->asignaturas ) > 0) {
				unset($grupos_all[$keyGr]);
			}else{
				array_push($res, $grupos_all[$keyGr]);
			}
		}


		return $res;
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


			$asignatura->periodosProm = Periodo::where('year_id', '=', $user->year_id)->get();

			// A cada alumno le daremos los periodos y la definitiva de cada periodo
			foreach ($alumnos as $keyAl => $alumno) {

				$periodosTemp = Periodo::where('year_id', '=', $user->year_id)->get();

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