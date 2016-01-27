<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;

use App\Models\Nota;
use App\Models\User;

class Subunidad extends Model {
	use SoftDeletes;
	
	protected $fillable = [];
	protected $table = 'subunidades';

	protected $dates = ['deleted_at', 'created_at'];
	protected $softDelete = true;



	public static function deUnidad($unidad_id)
	{
		$consulta = 'SELECT s.id as subunidad_id, s.definicion as definicion_subunidad, s.porcentaje as porcentaje_subunidad,
						s.nota_default, s.orden as orden_subunidad, s.inicia_at, s.finaliza_at
					FROM subunidades s
					where s.unidad_id=:unidad_id and s.deleted_at is null
					order by s.orden';

		$unidades = DB::select(DB::raw($consulta), array(
			':unidad_id'	=> $unidad_id
		));

		return $unidades;
	}

	public static function notas($subunidad_id)
	{
		$notas = Nota::where('subunidad_id', '=', $subunidad_id)->get();
		return $notas;
	}

	public static function perdidasDeUnidad($unidad_id, $alumno_id)
	{
		$consulta = 'SELECT s.id as subunidad_id, s.definicion as definicion_subunidad, s.porcentaje as porcentaje_subunidad,
						s.nota_default, s.orden as orden_subunidad, n.id as nota_id, n.nota
					FROM subunidades s
					inner join notas n on n.subunidad_id=s.id and n.alumno_id=:alumno_id and n.nota<:nota_minima
					where s.unidad_id=:unidad_id and s.deleted_at is null';

		$subunidades = DB::select(DB::raw($consulta), array(
			':alumno_id'	=> $alumno_id,
			':nota_minima'	=> User::$nota_minima_aceptada,
			':unidad_id'	=> $unidad_id,
		));

		return $subunidades;
	}
}