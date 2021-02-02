<?php namespace App\Http\Controllers;



use Request;
use DB;
use Hash;

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
use App\Models\Area;
use \Log;

use Carbon\Carbon;


class PromovidosController extends Controller {

	public $user;
	private $escalas_val = [];

	public function __construct()
	{
		$this->user = User::fromToken();
	}

	public function putCalcularGrupo()
	{
		$year_id 		= $this->user->year_id;
		$year_actual 	= true;
		$periodo_a_calcular = 4;
		$grupo_id 		= Request::input('grupo_id');
		
		
		$year			= Year::datos($year_id);
		$alumnos		= Grupo::alumnos($grupo_id);
		
		$this->escalas_val = DB::select('SELECT * FROM escalas_de_valoracion WHERE year_id=? AND deleted_at is null', [$this->user->year_id]);

		$year->periodos = DB::select('SELECT * FROM periodos WHERE year_id=? and deleted_at is null', [$this->user->year_id]);

		$response_alumnos = [];
		

		foreach ($alumnos as $alumno) {

			// Todas las materias con sus unidades y subunides
			$this->definitivasMateriasXPeriodo($alumno, $grupo_id, $this->user->year_id, $year->periodos, $this->user->si_recupera_materia_recup_indicador );

			
			
			$consulta = 'SELECT r.*, m.materia, m.alias, m.area_id FROM recuperacion_final r 
				INNER JOIN asignaturas a ON a.id=r.asignatura_id and a.deleted_at is null
				INNER JOIN materias m ON m.id=a.materia_id and m.deleted_at is null
				WHERE alumno_id=? and year=?';
				
			$alumno->recuperaciones = DB::select($consulta, [$alumno->alumno_id, $this->user->year]);

			$canti_recu = count($alumno->recuperaciones);
			for ($k=0; $k < $canti_recu; $k++) { 
				$recu = $alumno->recuperaciones[$k];
				
				$consulta = 'SELECT ar.* FROM areas ar 
					INNER JOIN materias m ON m.area_id=ar.id and m.deleted_at is null
					INNER JOIN asignaturas a ON a.materia_id=m.id and a.deleted_at is null
					WHERE ar.id=? and ar.deleted_at is null';
					
				$canti_asignaturas_en_area = count(DB::select($consulta, [$recu->area_id]));
				
				if ($canti_asignaturas_en_area > 0) {
					$recu->es_area = true;
	
					$alumno->cant_lost_areas = $alumno->cant_lost_areas - 1;
				}
			}

			
			$alumno->cant_lost_asig = $alumno->cant_lost_asig - count($alumno->recuperaciones);

	
			$asignaturas_perdidas = $this->asignaturasPerdidasDeAlumno($alumno, $grupo_id, $this->user->year_id);

			if (count($asignaturas_perdidas) > 0) {
				
				$alumno->asignaturas_perdidas = $asignaturas_perdidas;
				$alumno->notas_perdidas_year = 0;
				
				if ($periodo_a_calcular) {
					$alumno->periodos_con_perdidas = DB::select('SELECT * FROM periodos WHERE year_id=? and numero<=? and deleted_at is null', [$this->user->year_id, $periodo_a_calcular]);
				}else{
					$alumno->periodos_con_perdidas = DB::select('SELECT * FROM periodos WHERE year_id=? and deleted_at is null', [$this->user->year_id]);
				}

				foreach ($alumno->periodos_con_perdidas as $keyPerA => $periodoAlone) {

					$periodoAlone->cant_perdidas = 0;
					
					foreach ($alumno->asignaturas_perdidas as $keyAsig => $asignatura_perdida) {

						foreach ($asignatura_perdida->periodos as $keyPer => $periodo) {

							if ($periodoAlone->id == $periodo->id) {
								if ($periodo->id == $periodoAlone->id) {
									$periodoAlone->cant_perdidas += $periodo->cantNotasPerdidas;
								}
								
							}
						}
					}

					$alumno->notas_perdidas_year += $periodoAlone->cant_perdidas;
					
				}
			}



			//****************************
			// Guardamos todos los calculos
			
			$diagnostico = "Automático";

			if ($year->cant_areas_pierde_year > 0 && $alumno->cant_lost_areas > 0  && $alumno->cant_lost_areas < $year->cant_areas_pierde_year) {
				$diagnostico = "Promoción pendiente (calculado)";
			}
			if ($year->cant_areas_pierde_year > 0 && $alumno->cant_lost_areas == 0) {
				$diagnostico = "Promovido (calculado)";
			}
			if ($year->cant_areas_pierde_year > 0 && $alumno->cant_lost_areas >= $year->cant_areas_pierde_year) {
				$diagnostico = "No promovido (calculado)";
				Log::info($alumno->cant_lost_areas);
			}

			if ($alumno->cant_lost_asig > 0 && $alumno->cant_lost_asig < $year->cant_asignatura_pierde_year && $year->cant_asignatura_pierde_year > 0) {
				$diagnostico = "Promoción pendiente (calculado)";
			}
			if ($year->cant_asignatura_pierde_year > 0 && ($alumno->cant_lost_asig == 0 || $alumno->cant_lost_asig >= $year->cant_asignatura_pierde_year)) {
				$diagnostico = "Promovido (calculado)";
			}
			if ($alumno->cant_lost_asig >= $year->cant_asignatura_pierde_year) {
				$diagnostico = "No promovido (calculado)";
			}

			$alumno->promovido = $diagnostico;

			$consulta = "UPDATE matriculas 
				SET promovido=:promovido, promedio=:promedio, cant_asign_perdidas=:cant_asign_perdidas, cant_areas_perdidas=:cant_areas_perdidas
				WHERE id=:matricula_id AND promovido NOT LIKE '%(manual)%'";

			$res = DB::update($consulta, [
				':promovido' => $diagnostico,
				':promedio' => $alumno->promedio,
				':cant_asign_perdidas' => $alumno->cant_lost_asig,
				':cant_areas_perdidas' => $alumno->cant_lost_areas,
				':matricula_id' => $alumno->matricula_id,
			]);
			
			
		}


		foreach ($alumnos as $alumno) {
			
			$alumno->puesto = Nota::puestoAlumno($alumno->promedio, $alumnos);
			

		}


		return $alumnos;
		
	}



	

	public function definitivasMateriasXPeriodo(&$alumno, $grupo_id, $year_id, $periodos, $si_recupera_materia_recup_indicador=false)
	{

		$alumno->asignaturas	= Grupo::detailed_materias($grupo_id);

		$alumno->promedio = 0;
		$alumno->cant_lost_asig = 0;
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
					
			
			$paramentros = [
				':year_id'		=> $year_id,
				':alumno_id'	=> $alumno->alumno_id, 
				':asignatura_id'=> $asignatura->asignatura_id
			];
				
			
			$asignatura->definitivas = DB::select($consulta, $paramentros);


			$suma_def = 0;
			$notas_perd = 0;
			
			foreach ($asignatura->definitivas as $keydef => $definitiva) {
				
				
				$suma_def += (float)$definitiva->DefMateria;
				
				
				if(($si_recupera_materia_recup_indicador && $definitiva->DefMateria >= User::$nota_minima_aceptada) || ( $definitiva->manual==1 && $definitiva->DefMateria >= User::$nota_minima_aceptada)){
					// No se cuentan las notas perdidas
				}else{
					
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
				
			}
			if(count($asignatura->definitivas)){
				$asignatura->promedio 			= $suma_def / count($asignatura->definitivas);
				$asignatura->nota_asignatura 	= $asignatura->promedio;
				$asignatura->notas_perdidas 	= $notas_perd;

			}else{
				$asignatura->promedio 			= 0;
				$asignatura->nota_asignatura 	= 0;
				$asignatura->notas_perdidas 	= $notas_perd;

			}
			

			$escala = $this->valoracion($asignatura->promedio);

			if ($escala) {
				$asignatura->desempenio 	= $escala->desempenio;
				$asignatura->perdido 		= $escala->perdido;
				$asignatura->valoracion 	= $escala->valoracion;
			}
			

			$alumno->promedio += $asignatura->promedio;
			$alumno->notas_perdidas += $asignatura->notas_perdidas;



			// Si es un promedio perdido, debo sumarlo como una asignatura perdida
			if (round($asignatura->promedio) < User::$nota_minima_aceptada) {
				$alumno->cant_lost_asig += 1;
			}

		}
		
		if (count($alumno->asignaturas) > 0) {
			$alumno->promedio = $alumno->promedio / count($alumno->asignaturas);
		}else{
			$alumno->promedio = 0;
		}
		

		
		// Agrupamos por áreas
		$areas = Area::agrupar_asignaturas($grupo_id, $alumno->asignaturas, $this->escalas_val);		
		$cant_lost_areas = 0;
		
		for ($k=0; $k < count($areas); $k++) { 
			if ($areas[$k]->area_nota < User::$nota_minima_aceptada){
				$cant_lost_areas = $cant_lost_areas + 1;
			}
		}
		
		$alumno->areas 				= $areas;
		$alumno->cant_lost_areas 	= $cant_lost_areas;


		
		return $alumno;
	}



	public function asignaturasPerdidasDeAlumno($alumno, $grupo_id, $year_id)
	{
		$asignaturas	= Grupo::detailed_materias($grupo_id);


		foreach ($asignaturas as $keyAsig => $asignatura) {
			$periodo_a_calcular = Request::input('periodo_a_calcular');
			
			if ($periodo_a_calcular) {
				$asignatura->periodos = DB::select('SELECT * FROM periodos WHERE year_id=? and numero<=? and deleted_at is null', [$year_id, $periodo_a_calcular]);
			}else{
				$asignatura->periodos = DB::select('SELECT * FROM periodos WHERE year_id=? and deleted_at is null', [$year_id]);;
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





}