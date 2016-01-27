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

	public static function matricularUno($alumno_id, $grupo_id)
	{

		$year = Year::where('actual', '=', true)->first();
		$consulta = 'SELECT m.id, m.alumno_id, m.grupo_id, m.matriculado, g.year_id 
			FROM matriculas m 
			inner join grupos g 
				on m.alumno_id = :alumno_id and g.year_id = :year_id and m.grupo_id=g.id';

		$matriculas = DB::select(DB::raw($consulta), array('alumno_id'=>$alumno_id, 'year_id'=>$year->id));
		$matricula=false;

		for ($i=0; $i < count($matriculas); $i++) { 

			$matri = Matricula::onlyTrashed()->where('id', $matriculas[$i]->id)->first();
		/*
		$queries = DB::getQueryLog();
		$last_query = end($queries);
		return $last_query;
		*/
			if ($matri) {
				if ($matri->grupo_id == $grupo_id) {
					if ($matricula) { // Si ya he encontrado en un elemento anterior una matrícula identica, es por que ya la he activado, no debo activar más. Por el contrario, debo borrarlas
						$$matri->delete();
					}else{
						$matri->matriculado = true;
						$matri->fecha_retiro = null;
						$matri->save();
						$matri->restore();
						$matricula=$matri;
					}
				}else{
					$matri->delete();
				}
			}
			
			
		}
		
		
		try {
			if (!$matricula) {
				$matricula = new Matricula;
				$matricula->alumno_id 	= $alumno_id;
				$matricula->grupo_id	= $grupo_id;
				$matricula->matriculado	= true;
				$matricula->save();
			}
			
		} catch (Exception $e) {
			// se supone que esto nunca va a ocurrir, ya que eliminé todas las matrículas 
			// excepto la que concordara con el grupo, poniéndola en matriculado=true
			$matricula = Matricula::where('alumno_id', '=', $alumno_id)->where('grupo_id', '=', $grupo_id)->first();
			$matricula->matriculado = true;
			$matricula->save();
		}

		return $matricula;
	}


	public function alumnos()
	{
		return $this->hasMany('Alumno');
	}

}



