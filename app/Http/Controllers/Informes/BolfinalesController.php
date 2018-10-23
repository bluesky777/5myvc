<?php namespace App\Http\Controllers\Informes;


use App\Http\Controllers\Controller;


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
use App\Models\Nota;
use App\Models\ConfigCertificado;
use App\Models\EscalaDeValoracion;
use App\Models\Debugging;
use App\Models\NotaComportamiento;
use \Log;


class BolfinalesController extends Controller {


	private $escalas_val = [];



	public function putDetailedNotasYearGroup($grupo_id)
	{
		$user = User::fromToken();

		$boletines = $this->detailedNotasGrupo($grupo_id, $user);

		//$grupo->alumnos = $alumnos;
		//$grupo->asignaturas = $asignaturas;
		//return (array)$grupo;

		return $boletines;


	}



	public function putDetailedNotasYear($grupo_id)
	{
		$user = User::fromToken();

		
		$requested_alumnos = '';

		if (Request::has('requested_alumnos')) {
			$requested_alumnos = Request::get('requested_alumnos');
		}

		$boletines = $this->detailedNotasGrupo($grupo_id, $user, $requested_alumnos);

		//$grupo->alumnos = $alumnos;
		//$grupo->asignaturas = $asignaturas;
		//return (array)$grupo;

		return $boletines;


	}

	public function detailedNotasGrupo($grupo_id, $user, $requested_alumnos='')
	{

		$this->escalas_val = DB::select('SELECT * FROM escalas_de_valoracion WHERE year_id=? AND deleted_at is null', [$user->year_id]);
		
		$year_actual = true;
		if (Request::has('year_selected')) {
			if (Request::input('year_selected') == true || Request::input('year_selected') == 'true') {
				$year_actual = false;
			}
		}
		
		
		
		
		
		$grupo			= Grupo::datos($grupo_id);
		$year			= Year::datos($user->year_id, $year_actual);
		$alumnos		= Grupo::alumnos($grupo_id, $requested_alumnos);
		
		$periodo_a_calcular = Request::input('periodo_a_calcular');
		
		if ($periodo_a_calcular) {
			Log::info('detailedNotasGrupo: ' . $periodo_a_calcular);
			$year->periodos = Periodo::where('year_id', $user->year_id)->where('numero', '<=', $periodo_a_calcular)->get();
		}else{
			$year->periodos = Periodo::where('year_id', $user->year_id)->get();
		}

		$cons = 'SELECT c.*, i.nombre as encabezado_nombre, i2.nombre as piepagina_nombre 
				FROM config_certificados c 
				left join images i on i.id=c.encabezado_img_id and i.deleted_at is null
				left join images i2 on i2.id=c.piepagina_img_id and i2.deleted_at is null
					where c.id=?';
		$config_certificado = DB::select($cons, [$year->config_certificado_estudio_id]);
		if (count($config_certificado) > 0) {
			$year->config_certificado = $config_certificado[0];
		}


		$cons = 'SELECT n.nombre as nivel_educativo FROM niveles_educativos n
				inner join grados gra on gra.nivel_educativo_id=n.id and gra.deleted_at is null
				inner join grupos gru on gru.grado_id=gra.id and gru.id=? and gru.deleted_at is null
				where n.deleted_at is null';

		$niveles = DB::select($cons, [$grupo_id]);
		if (count($niveles) > 0) {
			$grupo->nivel_educativo = $niveles[0]->nivel_educativo;
		}



		


		$grupo->cantidad_alumnos = count($alumnos);

		$response_alumnos = [];
		

		foreach ($alumnos as $alumno) {

			// Todas las materias con sus unidades y subunides
			$this->definitivasMateriasXPeriodo($alumno, $grupo_id, $user->year_id, $year->periodos);


			$asignaturas_perdidas = $this->asignaturasPerdidasDeAlumno($alumno, $grupo_id, $user->year_id);

			if (count($asignaturas_perdidas) > 0) {
				
				$alumno->asignaturas_perdidas = $asignaturas_perdidas;
				$alumno->notas_perdidas_year = 0;
				$alumno->periodos_con_perdidas = Periodo::where('year_id', $user->year_id)->get();

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

	public function definitivasMateriasXPeriodo(&$alumno, $grupo_id, $year_id, $periodos)
	{

		$alumno->asignaturas	= Grupo::detailed_materias($grupo_id);

		$alumno->promedio = 0;
		$alumno->cant_lost_asig = 0;
		$alumno->ausencias = 0;
		$alumno->tardanzas = 0;
		$alumno->total_creditos = 0;
		$alumno->notas_perdidas = 0;


		foreach ($alumno->asignaturas as $asignatura) {

			$alumno->total_creditos += $asignatura->creditos;
						
			$consulta = 'SELECT nf.*, nf.nota as DefMateria, aus.cantidad_ausencia, tar.cantidad_tardanza
						FROM notas_finales nf
						INNER JOIN periodos p on p.year_id=:year_id and p.id=nf.periodo_id and p.deleted_at is null
						left join (
								select count(au.id) as cantidad_ausencia, au.alumno_id, au.periodo_id, au.asignatura_id
								from ausencias au 
								where au.deleted_at is null and au.cantidad_ausencia > 0
								group by au.alumno_id, au.periodo_id, au.asignatura_id
								
								)as aus on aus.alumno_id=nf.alumno_id and aus.asignatura_id=nf.asignatura_id and aus.periodo_id=nf.periodo_id
						left join (
								select count(au.id) as cantidad_tardanza, au.alumno_id, au.periodo_id, au.asignatura_id
								from ausencias au 
								where au.deleted_at is null and au.cantidad_tardanza > 0
								group by au.alumno_id, au.periodo_id, au.asignatura_id
									
						)as tar on tar.alumno_id=nf.alumno_id and tar.asignatura_id=nf.asignatura_id and tar.periodo_id=nf.periodo_id
						WHERE nf.alumno_id=:alumno_id and nf.asignatura_id=:asignatura_id
						ORDER BY nf.periodo';
						
						
			//User::$nota_minima_aceptada
			$asignatura->definitivas = DB::select($consulta, [
										':year_id'		=> $year_id,
										':alumno_id'	=> $alumno->alumno_id, 
										':asignatura_id'=> $asignatura->asignatura_id
									]);




			// Agrego Periodos ficticios al array para llenar la tabla con espacios vacios.
			$per_faltantes = count($periodos) - count($asignatura->definitivas);

			if($per_faltantes > 0){
				for($i=0; $i<$per_faltantes; $i++){
					$prov = (object)['DefMateria'=>0,'cantidad_ausencia'=>0,'cantidad_tardanza'=>0,'periodo_id'=>-1];
					array_push($asignatura->definitivas, $prov);
				}
			}


			// Hallamos las ausencias y tardanzas
			$suma_def = 0;
			$suma_aus = 0;
			$suma_tar = 0;
			$notas_perd = 0;
			
			foreach ($asignatura->definitivas as $keydef => $definitiva) {
				$suma_def += (float)$definitiva->DefMateria;
				$suma_aus += (int)$definitiva->cantidad_ausencia;
				$suma_tar += (int)$definitiva->cantidad_tardanza;


				// Cuantas notas tiene perdidas por cada definitiva
				$consul = 'SELECT COUNT(n.id) as notas_perdidas
							from notas n
							inner join subunidades s on s.id=n.subunidad_id and s.deleted_at is null
							inner join unidades u on u.id=s.unidad_id and u.periodo_id=:periodo_id and u.asignatura_id=:asignatura_id and u.deleted_at is null
							where n.nota < :nota_minima and n.alumno_id=:alumno_id;';

				$definitiva->notas_perdidas = DB::select(DB::raw($consul), array(
										':periodo_id'	=> $definitiva->periodo_id,
										':asignatura_id'=> $asignatura->asignatura_id,
										':nota_minima'	=> User::$nota_minima_aceptada,
										':alumno_id'	=> $alumno->alumno_id ));

				if (count($definitiva->notas_perdidas) > 0) {
					$definitiva->notas_perdidas = $definitiva->notas_perdidas[0]->notas_perdidas;
					$notas_perd += $definitiva->notas_perdidas;
				}
			}
			$asignatura->promedio = $suma_def / count($asignatura->definitivas);
			$asignatura->ausencias = $suma_aus;
			$asignatura->tardanzas = $suma_tar;
			$asignatura->notas_perdidas = $notas_perd;

			$escala = $this->valoracion($asignatura->promedio);

			if ($escala) {
				$asignatura->desempenio 	= $escala->desempenio;
				$asignatura->perdido 		= $escala->perdido;
				$asignatura->valoracion 	= $escala->valoracion;
			}
			

			$alumno->promedio += $asignatura->promedio;
			$alumno->ausencias += $asignatura->ausencias;
			$alumno->tardanzas += $asignatura->tardanzas;
			$alumno->notas_perdidas += $asignatura->notas_perdidas;



			// Si es un promedio perdido, debo sumarlo como una asignatura perdida
			if (round($asignatura->promedio) < User::$nota_minima_aceptada) {
				$alumno->cant_lost_asig += 1;
			}

		}

		$alumno->promedio = $alumno->promedio / count($alumno->asignaturas);

		$escala = $this->valoracion($alumno->promedio);
		if ($escala) {
			$alumno->desempenio = $escala->desempenio;
		}


		// Nota promedio de comportamiento
		$alumno->nota_comportamiento_year = NotaComportamiento::nota_promedio_year($alumno->alumno_id);
		

		return $alumno;
	}




	public function valoracion($nota)
	{
		$nota = round($nota);

		foreach ($this->escalas_val as $key => $escala_val) {
			//Debugging::pin($escala_val->porc_inicial, $escala_val->porc_final, $nota);

			if (($escala_val->porc_inicial <= $nota) &&  ($escala_val->porc_final >= $nota)) {
				return $escala_val;
			}
		}
		return [];
	}



	public function asignaturasPerdidasDeAlumno($alumno, $grupo_id, $year_id)
	{
		$asignaturas	= Grupo::detailed_materias($grupo_id);


		foreach ($asignaturas as $keyAsig => $asignatura) {
			$periodo_a_calcular = Request::input('periodo_a_calcular');
			
			if ($periodo_a_calcular) {
				//Log::info('$periodo_a_calcular ' . $periodo_a_calcular);
				$asignatura->periodos = Periodo::where('year_id', $year_id)->where('numero', '<=', $periodo_a_calcular)->get();
			}else{
				$asignatura->periodos = Periodo::where('year_id', $year_id)->get();
			}
			
			

			$asignatura->cantTotal = 0;

			foreach ($asignatura->periodos as $keyPer => $periodo) {

				
				$consulta = 'SELECT distinct n.nota, n.id as nota_id, n.alumno_id,  s.id as subunidad_id, s.definicion, u.id as unidad_id, u.periodo_id
						from notas n, subunidades s, unidades u, asignaturas a, matriculas m
						where n.subunidad_id=s.id and s.unidad_id=u.id and u.periodo_id=:periodo_id 
						and u.asignatura_id=a.id and m.alumno_id=n.alumno_id and m.deleted_at is null and m.estado="MATR"
						and a.id=:asignatura_id and n.alumno_id=:alumno_id and n.nota < :nota_minima;';

				$notas_perdidas = DB::select(DB::raw($consulta), array(
									':periodo_id'		=> $periodo->id, 
									':asignatura_id'	=> $asignatura->asignatura_id, 
									':alumno_id'		=> $alumno->alumno_id,
									':nota_minima'		=> User::$nota_minima_aceptada
								));

				$periodo->cantNotasPerdidas = count($notas_perdidas);

				$asignatura->cantTotal += $periodo->cantNotasPerdidas;


				if ($periodo->cantNotasPerdidas == 0) {
					unset($asignatura->periodos[$keyPer]);
				}
				
				
			}

			if (count($asignatura->periodos) == 0) {
				unset($asignaturas[$keyAsig]);
			}

			$hasPeriodosConPerdidas = false;

			foreach ($asignatura->periodos as $keyPer => $periodo) {
				if ($periodo->cantNotasPerdidas > 0) {
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







}