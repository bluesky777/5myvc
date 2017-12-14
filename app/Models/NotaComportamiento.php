<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;


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


	public static function nota_comportamiento($alumno_id, $periodo_id){
		
		$consulta = 'SELECT * FROM nota_comportamiento n WHERE n.alumno_id=:alumno_id and n.periodo_id=:periodo_id and n.deleted_at is null';
		$nota = DB::select($consulta, [
										':alumno_id'	=>$alumno_id, 
										':periodo_id'	=>$periodo_id
									]);
		
		if(count($nota) > 0){
			return $nota[0];
		}else{
			return [];
		}

		 
	}


	public static function nota_promedio_year($alumno_id){
		
		$consulta 	= 'SELECT avg(nota) as nota_comportamiento_year FROM nota_comportamiento n WHERE n.alumno_id=:alumno_id and n.deleted_at is null';
		$nota 		= DB::select($consulta, [ ':alumno_id' =>$alumno_id ]);
		
		if(count($nota) > 0){
			return (int)$nota[0]->nota_comportamiento_year;
		}else{
			return 0;
		}

		 
	}

}