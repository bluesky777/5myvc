<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;

class Unidad extends Model {
	use SoftDeletes;
	
	protected $fillable = [];
	protected $table = 'unidades';

	protected $dates = ['deleted_at', 'created_at'];
	protected $softDelete = true;




	public function subunidades()
	{
		return $this->hasMany('Subunidad');
	}

	public static function deAsignatura($asignatura_id, $periodo_id)
	{
		$consulta = 'SELECT u.id as unidad_id, u.definicion as definicion_unidad, u.porcentaje as porcentaje_unidad, 
						u.asignatura_id, u.orden as orden_unidad, u.periodo_id
					FROM unidades u
					where u.asignatura_id=:asignatura_id and u.periodo_id=:periodo_id and u.deleted_at is null
					order by u.orden, u.id';

		$unidades = DB::select(DB::raw($consulta), array(
			':asignatura_id'	=> $asignatura_id,
			':periodo_id'		=> $periodo_id
		));

		return $unidades;
	}


	public static function informacionAsignatura($asignatura_id, $periodo_id)
	{
		$result = new \stdClass;

		
		$consulta = 'SELECT id, definicion, porcentaje
					FROM unidades
					where asignatura_id=:asignatura_id and periodo_id=:periodo_id and deleted_at is null
					order by orden';

		$unidades = DB::select($consulta, [
			':asignatura_id'	=> $asignatura_id,
			':periodo_id'		=> $periodo_id
		]);

		$porc_unidades = 0;
		$result->porc_subunidades_incorrecto = false;
		$result->porc_notas_incorrecto = false;

		foreach ($unidades as $unidad) {
			
			$porc_unidades += $unidad->porcentaje;

			$consulta = 'SELECT id, definicion, porcentaje
						FROM subunidades
						where unidad_id=:unidad_id and deleted_at is null
						order by orden';

			$unidad->subunidades = DB::select(DB::raw($consulta), array(
				':unidad_id'	=> $unidad->id,
			));

			$porc_subunidades = 0;

			foreach ($unidad->subunidades as $subunidad) {
				$porc_subunidades += $subunidad->porcentaje;

				$notas = Nota::where('subunidad_id', $subunidad->id)->get();

				$subunidad->cantNotas = count($notas);

				if ($subunidad->cantNotas == 0) {
					$result->porc_notas_incorrecto = true;
				}

			}

			$unidad->porc_subunidades = $porc_subunidades ;

			if ($unidad->porc_subunidades != 100) {
				$result->porc_subunidades_incorrecto = true;
			}

		}


		$result->porc_unidades = $porc_unidades;
		$result->items = $unidades;

		return $result;
	}

}