<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;

use App\Models\Year;

class Matricula extends Model {

	protected $table = 'matriculas';

	protected $fillable = [];

	use SoftDeletes;
	protected $softDelete = true;

	public static function matricularUno($alumno_id, $grupo_id, $year_id=false)
	{
		if (!$year_id) {
			$year = Year::where('actual', true)->first();
			$year_id = $year->id;
		}
		
		$consulta = 'SELECT m.id, m.alumno_id, m.grupo_id, m.estado, g.year_id 
			FROM matriculas m 
			inner join grupos g 
				on m.alumno_id = :alumno_id and g.year_id = :year_id and m.grupo_id=g.id';

		$matriculas = DB::select($consulta, ['alumno_id'=>$alumno_id, 'year_id'=>$year_id]);
		$matricula = false;

		for ($i=0; $i < count($matriculas); $i++) { 

			$matri = Matricula::onlyTrashed()->where('id', $matriculas[$i]->id)->first();
			/*
			$queries = DB::getQueryLog();
			$last_query = end($queries);
			return $last_query;
			*/
			if ($matri) {
				if ($matricula) { // Si ya he encontrado en un elemento anterior una matrícula identica, es porque ya la he activado, no debo activar más. Por el contrario, debo borrarlas
					$matri->delete();
				}else{
					$matri->estado 			= 'MATR'; // Matriculado, Asistente o Retirado
					$matri->fecha_retiro 	= null;
					$matri->grupo_id 		= $grupo_id;
					$matri->save();
					$matri->restore();
					$matricula=$matri;
				}
			}
		}
		
		
		try {
			if (!$matricula) {
				$matricula = new Matricula;
				$matricula->alumno_id 	= $alumno_id;
				$matricula->grupo_id	= $grupo_id;
				$matricula->estado 		= 'MATR';
				$matricula->save();
			}
			
		} catch (Exception $e) {
			// se supone que esto nunca va a ocurrir, ya que eliminé todas las matrículas 
			// excepto la que concordara con el grupo, poniéndola en estado=MATR
			$matricula 			= Matricula::where('alumno_id', $alumno_id)->where('grupo_id', $grupo_id)->first();
			$matricula->estado 	= 'MATR';
			$matricula->save();
		}

		return $matricula;
	}


	public function alumnos()
	{
		return $this->hasMany('Alumno');
	}

}



