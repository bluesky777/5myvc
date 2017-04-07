<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;

class Ausencia extends Model {
	protected $fillable = [];

	use SoftDeletes;
	protected $softDelete = true;


	public static function deAlumno($asignatura_id, $alumno_id, $periodo_id)
	{
		$consulta = 'SELECT id, cantidad_ausencia, cantidad_tardanza, tipo, fecha_hora, created_by, created_at 
					FROM ausencias 
					where alumno_id=? and asignatura_id=? and periodo_id=? and deleted_at is null';

		$ausencias = DB::select($consulta, [$alumno_id, $asignatura_id, $periodo_id] );
		return $ausencias;
	}

	public static function totalDeAlumno($alumno_id, $periodo_id)
	{
		$consulta = 'SELECT 
					    (SELECT COUNT(*) FROM ausencias a1 WHERE a1.alumno_id=a.alumno_id AND a1.periodo_id=a.periodo_id and a1.entrada=true and a1.tipo="tardanza" and deleted_at is null) AS cant_tardanzas_entrada,
						(SELECT COUNT(*) FROM ausencias a1 WHERE a1.alumno_id=a.alumno_id AND a1.periodo_id=a.periodo_id and a1.entrada=true and a1.tipo="ausencia" and deleted_at is null) AS cant_ausencias_entrada,
						(SELECT COUNT(*) FROM ausencias a1 WHERE a1.alumno_id=a.alumno_id AND a1.periodo_id=a.periodo_id and a1.entrada=false and a1.tipo="ausencia" and deleted_at is null) AS cant_ausencias_clases,
						(SELECT COUNT(*) FROM ausencias a1 WHERE a1.alumno_id=a.alumno_id AND a1.periodo_id=a.periodo_id and a1.entrada=false and a1.tipo="tardanza" and deleted_at is null) AS cant_tardanzas_clases
					FROM ausencias a
					WHERE alumno_id=? and periodo_id=? and deleted_at is null   
					group by a.alumno_id';

		$totales = DB::select($consulta, [$alumno_id, $periodo_id] );
		
		if (count($totales) > 0) {
			return $totales[0];
		}else{
			return ['cant_ausencias_clases'=> 0, 'cant_ausencias_entrada'=> 0, 'cant_tardanzas_clases'=> 0, 'cant_tardanzas_entrada'=> 0];
		}
	}

}