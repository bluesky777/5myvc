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
		$consulta = 'SELECT id, cantidad_ausencia, cantidad_tardanza, fecha_hora, created_by, created_at 
					FROM ausencias 
					where alumno_id=? and asignatura_id=? and periodo_id=? and deleted_at is null';

		$ausencias = DB::select(DB::raw($consulta), array($alumno_id, $asignatura_id, $periodo_id));
		return $ausencias;
	}

}