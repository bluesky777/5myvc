<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class NotaComportamiento extends Model {
	protected $fillable = ['alumno_id', 'periodo_id'];  // Para poder usar firstOrNew()
	protected $table = "nota_comportamiento";

	use SoftDeletes;
	protected $softDelete = true;


	public static function crearVerifNota($alumno_id, $periodo_id)
	{

		$nota = NotaComportamiento::firstOrNew(['alumno_id' => $alumno_id, 'periodo_id' => $periodo_id]);
		if (!$nota->id) {
			$nota->nota = 100;
			$nota->save();
		}

		return $nota;
	}

}