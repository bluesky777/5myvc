<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


use App\Models\Grupo;
use App\Models\Nota;
use App\Models\Periodo;
use App\Models\Alumno;
use App\Models\Unidad;
use App\Models\Subunidad;
use App\Models\Asignatura;

use \stdClass;
use DB;
use Carbon\Carbon;


class Nota extends Model {
	protected $fillable = [];

	use SoftDeletes;
	protected $softDelete = true;


	// Solo si la subunidad tiene cero notas
	public static function crearNotas($grupo_id, $subunidad, $user_id)
	{
		$alumnos 	= Grupo::alumnos($grupo_id);
		$now 		= Carbon::now('America/Bogota');

		foreach ($alumnos as $alumno) {
			DB::insert('INSERT INTO notas(subunidad_id, alumno_id, nota, created_by, created_at, updated_at) VALUES(?, ?, ?, ?, ?, ?)', [$subunidad->id, $alumno->alumno_id, $subunidad->nota_default, $user_id, $now, $now]);
		}

		return;
	}

	// Verificar cada alumno si tiene nota en la subunidad
	public static function verificarCrearNotas($grupo_id, $subunidad, $user_id)
	{
		$alumnos 	= Grupo::alumnos($grupo_id);
		$now 		= Carbon::now('America/Bogota');

		foreach ($alumnos as $alumno) {

			$notVerif = DB::select('SELECT * from notas WHERE subunidad_id=? and alumno_id=? and deleted_at is null', [$subunidad->id, $alumno->alumno_id]);

			if (count($notVerif) == 0) {
				DB::insert('INSERT INTO notas(subunidad_id, alumno_id, nota, created_by, created_at, updated_at) VALUES(?, ?, ?, ?, ?, ?)', [$subunidad->id, $alumno->alumno_id, $subunidad->nota_default, $user_id, $now, $now]);
			}
		}

		return;
	}

	// Verificar nota de un alumno si tiene o crearla
	public static function verificarCrearNota($alumno_id, $subunidad)
	{
		$notVerif = Nota::where('subunidad_id', $subunidad->id)
						->where('alumno_id', $alumno_id)->first();

		if ($notVerif) {
			$nota = $notVerif;
		}else{

			$nota = new Nota;
			$nota->alumno_id 		= $alumno->id;
			$nota->subunidad_id 	= $subunidad->id;
			$nota->nota 			= $subunidad->nota_default;

			$nota->save();

			$notVerif = $nota;
		}

		return $notVerif;
	}


	public static function puestoAlumno($promedio_alumno, $alumnos)
	{
		$puesto = 1;

		foreach ($alumnos as $alumno) {
			if ($alumno->promedio > $promedio_alumno) {
				$puesto += 1;
			}
		}

		return $puesto;
	}


	public static function alumnoPeriodosDetailed($alumno_id, $year_id, $profesor_id='')
	{
		$alumno 	= Alumno::alumnoData($alumno_id, $year_id);
		
		if (!$alumno) {
			return false;
		}
		
		$periodos 	= DB::select('SELECT * FROM periodos WHERE year_id=? and deleted_at is null', [ $year_id ]); 


		foreach ($periodos as $keyPer => $periodo) {
			
			$asignaturas = Grupo::detailed_materias_notafinal($alumno_id, $alumno->grupo_id, $periodo->id, $year_id);
			$sumatoria_asignaturas_per = 0;

			foreach ($asignaturas as $keyAsig => $asignatura) {
				
				if($profesor_id != $asignatura->profesor_id && $profesor_id != ''){
					unset($asignaturas[$keyAsig]);
				}else{

					$asignatura->unidades = Unidad::deAsignatura($asignatura->asignatura_id, $periodo->id);

					foreach ($asignatura->unidades as $unidad) {
						$unidad->subunidades = Subunidad::deUnidad($unidad->unidad_id);
					}

					Asignatura::calculoAlumnoNotas($asignatura, $alumno->alumno_id);
					$sumatoria_asignaturas_per += $asignatura->nota_asignatura; // Para sacar promedio del periodo
					
					$asignatura->ausencias	= Ausencia::deAlumno($asignatura->asignatura_id, $alumno->alumno_id, $periodo->id);
					$asignatura->frases		= FraseAsignatura::deAlumno($asignatura->asignatura_id, $alumno->alumno_id, $periodo->id);
				
				
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
			try {
				//$periodo->promedio = $sumatoria_asignaturas_per / count($alumno->asignaturas);
				$periodo->promedio = $sumatoria_asignaturas_per / count($asignaturas);
			} catch (Exception $e) {
				$periodo->promedio = 0;
			}

			$periodo->asignaturas = $asignaturas;

		}

		$alumno->periodos = $periodos;

		return $alumno;

	}



	public static function alumnoAsignaturasPeriodosDetailed($alumno_id, $year_id, $periodos_a_calcular='de_usuario', $periodo_usuario=0)
	{

		$alumno 		= Alumno::alumnoData($alumno_id, $year_id);
		$asignaturas 	= Grupo::detailed_materias($alumno->grupo_id);

		$sumatoria_asignaturas_year = 0;
		$sub_perdidas_year = 0;

		foreach ($asignaturas as $keyAsig => $asignatura) {

			$periodos = Periodo::hastaPeriodo($year_id, $periodos_a_calcular, $periodo_usuario);
			
			
			$sum_asignatura_year = 0;

			$subunidadesPerdidas = 0;

			foreach ($periodos as $keyPer => $periodo) {

				$asigna = new stdClass();
				$asigna->unidades = Unidad::deAsignatura($asignatura->asignatura_id, $periodo->id);

				foreach ($asigna->unidades as $unidad) {
					$unidad->subunidades = Subunidad::deUnidad($unidad->unidad_id);
				}

				Asignatura::calculoAlumnoNotas($asigna, $alumno->alumno_id);

				$sum_asignatura_year += $asigna->nota_asignatura;

				$subunidadesPerdidas += Asignatura::notasPerdidasAsignatura($asigna);
				
			}

			try {
				$asignatura->nota_asignatura_year = ($sum_asignatura_year / count($periodos));
				$asignatura->subunidadesPerdidas = $subunidadesPerdidas;
			} catch (Exception $e) {
				$asignatura->nota_asignatura_year = 0;
			}

			$asignatura->periodos = $periodos;

			$sumatoria_asignaturas_year += $asignatura->nota_asignatura_year;
			$sub_perdidas_year += $subunidadesPerdidas;


		}

		try {
			$alumno->promedio_year = ($sumatoria_asignaturas_year / count($asignaturas));
			$alumno->sub_perdidas_year = $sub_perdidas_year;
		} catch (Exception $e) {
			$alumno->promedio_year = 0;
		}

		$alumno->asignaturas = $asignaturas;

		return $alumno;

	}


}



