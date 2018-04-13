<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use App\Models\EscalaDeValoracion;


class Area extends Model {
	protected $fillable = [];

	use SoftDeletes;
	protected $softDelete = true;
	
	
	public static function agrupar_asignaturas($grupo_id, $asignaturas, $escalas)
	{
		
		// Agrupamos por áreas
		$consulta 	= 'SELECT ar.id as area_id, ar.orden, ar.nombre as area_nombre, ar.alias as area_alias
					FROM asignaturas a
					inner join materias m on m.id=a.materia_id and m.deleted_at is null
					inner join areas ar on ar.id=m.area_id and ar.deleted_at is null
					where a.deleted_at is null and a.grupo_id=? and a.profesor_id is not null
					group by ar.id order by ar.orden';
					
		$areas 		= DB::select($consulta, [ $grupo_id ]);
		$cantAr 	= count($areas);
		$cantAs 	= count($asignaturas);
		
		for ($i=0; $i < $cantAr; $i++) { 
			$found = 0;
			$areas[$i]->sumatoria 		= 0;
			$areas[$i]->asignaturas 	= [];
			
			for ($j=0; $j < $cantAs; $j++) { 
				if ($areas[$i]->area_id == $asignaturas[$j]->area_id) {
					$found += 1;
					$areas[$i]->sumatoria += $asignaturas[$j]->nota_asignatura;
					array_push($areas[$i]->asignaturas, $asignaturas[$j]);
				}
			}
			
			$areas[$i]->cant 				= $found;
			$areas[$i]->area_nota 			= round($areas[$i]->sumatoria / $found);
			$areas[$i]->area_desempenio 	= EscalaDeValoracion::valoracion($areas[$i]->area_nota, $escalas)->desempenio;
		}
		return $areas;
	}
	
	
	
	public static function agrupar_asignaturas_periodos($grupo_id, $asignaturas, $escalas, $num_periodo)
	{
		
		// Agrupamos por áreas
		$consulta 	= 'SELECT ar.id as area_id, ar.orden, ar.nombre as area_nombre, ar.alias as area_alias
					FROM asignaturas a
					inner join materias m on m.id=a.materia_id and m.deleted_at is null
					inner join areas ar on ar.id=m.area_id and ar.deleted_at is null
					where a.deleted_at is null and a.grupo_id=? and a.profesor_id is not null
					group by ar.id order by ar.orden';
					
		$areas 		= DB::select($consulta, [ $grupo_id ]);
		$cantAr 	= count($areas);
		$cantAs 	= count($asignaturas);
		
		for ($i=0; $i < $cantAr; $i++) { 
			$found = 0;
			$areas[$i]->sumatoria_per1 	= 0;
			$areas[$i]->sumatoria_per2 	= 0;
			$areas[$i]->sumatoria_per3 	= 0;
			$areas[$i]->sumatoria_per4 	= 0;
			$areas[$i]->asignaturas 	= [];
			
			for ($j=0; $j < $cantAs; $j++) { 
				if ($areas[$i]->area_id == $asignaturas[$j]->area_id) {
					$found += 1;
					
					if (isset($asignaturas[$j]->nota_final_per1)) {
						$areas[$i]->sumatoria_per1 += $asignaturas[$j]->nota_final_per1;
					}
					if (isset($asignaturas[$j]->nota_final_per2)) {
						$areas[$i]->sumatoria_per2 += $asignaturas[$j]->nota_final_per2;
					}
					if (isset($asignaturas[$j]->nota_final_per3)) {
						$areas[$i]->sumatoria_per3 += $asignaturas[$j]->nota_final_per3;
					}
					if (isset($asignaturas[$j]->nota_final_per4)) {
						$areas[$i]->sumatoria_per4 += $asignaturas[$j]->nota_final_per4;
					}
					
					array_push($areas[$i]->asignaturas, $asignaturas[$j]);
				}
			}
			
			$areas[$i]->cant = $found;
			
			$areas[$i]->per1_nota 			= round($areas[$i]->sumatoria_per1 / $found);
			$areas[$i]->desempenio_per1 	= EscalaDeValoracion::valoracion($areas[$i]->per1_nota, $escalas)->desempenio;

			if ($num_periodo > 1) {
				$areas[$i]->per2_nota 			= round($areas[$i]->sumatoria_per2 / $found);
				$areas[$i]->desempenio_per2 	= EscalaDeValoracion::valoracion($areas[$i]->per2_nota, $escalas)->desempenio;
			}
			if ($num_periodo > 2) {
				$areas[$i]->per3_nota 			= round($areas[$i]->sumatoria_per3 / $found);
				$areas[$i]->desempenio_per3 	= EscalaDeValoracion::valoracion($areas[$i]->per3_nota, $escalas)->desempenio;
			}
			if ($num_periodo == 4) {
				$areas[$i]->per4_nota 			= round($areas[$i]->sumatoria_per4 / $found);
				$areas[$i]->desempenio_per4 	= EscalaDeValoracion::valoracion($areas[$i]->per4_nota, $escalas)->desempenio;
			}
			//$areas[$i]->area_nota 			= round($areas[$i]->sumatoria / $found);
			//$areas[$i]->area_desempenio 	= EscalaDeValoracion::valoracion($areas[$i]->area_nota, $escalas)->desempenio;
		}
		return $areas;
	}
	
	
}