<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use \stdClass;

class Periodo extends Model {
	protected $fillable = [];

	use SoftDeletes;
	protected $softDelete = true;


	public static function hastaPeriodo($year_id, $periodos_a_calcular='de_usuario', $numero_periodo=0)
	{
		$periodos = new stdClass();


		// Solo los periodos pasados hasta EL ACTUAL si así fue solicitado
		if ($periodos_a_calcular == 'de_colegio') {
			$periodo_actual = Periodo::where('actual', true)
									->where('year_id', $year_id)->first();

			$periodos = Periodo::where('numero', '<=', $periodo_actual->numero)
								->where('year_id', '=', $year_id)->get();


		// Solo los periodos pasados hasta EL DE EL USUARIO
		}elseif($periodos_a_calcular == 'de_usuario'){
			$periodos = Periodo::where('numero', '<=', $numero_periodo)
								->where('year_id', '=', $year_id)->get();

		}elseif($periodos_a_calcular == 'todos'){
			$periodos = Periodo::where('year_id', '=', $year_id)->get();
		}

		return $periodos;
	}
}