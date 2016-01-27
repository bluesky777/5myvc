<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



use Request;
use DB;


class VtVoto extends Model {
	protected $fillable = [];

	use SoftDeletes;
	protected $softDelete = true;

	public static function verificarNoVoto($aspira_id, $participante_id)
	{
		$consulta = 'SELECT vv.id, vv.participante_id, vv.locked, vv.candidato_id
			from vt_votos vv
			inner join vt_candidatos vc on vc.id=vv.candidato_id 
				and vc.aspiracion_id=:aspiracion_id and vv.participante_id=:participante_id';

		$datos = array(':aspiracion_id' => $aspira_id, ':participante_id' => $participante_id);
		$votos = DB::select(DB::raw($consulta), $datos);
		
		foreach ($votos as $voto) {
			$voto = VtVoto::destroy($voto->id);
		}
	}

	public static function hasVoted($votacion_id, $participante_id)
	{
		// Función que define si el participante ha hecho algún voto (no necesariamente en todas las aspiraciones del evento).
		$consulta = 'SELECT vv.id as voto_id, vv.candidato_id, va.aspiracion FROM vt_votos vv 
			inner join vt_candidatos vc on vv.candidato_id=vc.id
			inner join vt_aspiraciones va on va.id=vc.aspiracion_id 
				and vv.participante_id=:participante_id and va.votacion_id=:votacion_id';

		$datos = array(':participante_id' => $participante_id, ':votacion_id' => $votacion_id);
		$votos = DB::select(DB::raw($consulta), $datos);
		
		if ( count($votos) > 0 ) {
			return true;
		}else{
			return false;
		}
	}

	public static function votesInAspiracion($aspiracion_id, $participante_id)
	{
		// Función que define si el participante ha hecho algún voto (no necesariamente en todas las aspiraciones del evento).

		$consulta = 'SELECT vv.id as voto_id, vv.candidato_id, vc.aspiracion_id FROM vt_votos vv 
			inner join vt_candidatos vc on vv.candidato_id=vc.id
				and vv.participante_id=:participante_id and vc.aspiracion_id=:aspiracion_id';

		$datos = array(':participante_id' => $participante_id, ':aspiracion_id' => $aspiracion_id);
		$votos = DB::select(DB::raw($consulta), $datos);
		
		return $votos;
	}

	public static function deCandidato($candidato_id, $aspiracion_id)
	{
		// Función que define si el participante ha hecho algún voto (no necesariamente en todas las aspiraciones del evento).

		$consulta = 'SELECT count(*) as cantidad, t.total from vt_votos vv
				inner join (
					select count(*) as total from vt_votos 
					inner join vt_candidatos vc on vc.id=vt_votos.candidato_id and vc.aspiracion_id=:aspiracion_id
				)t  where vv.candidato_id=:candidato_id';

		$datos = array(':aspiracion_id' => $aspiracion_id, ':candidato_id' => $candidato_id);
		$votos = DB::select(DB::raw($consulta), $datos);
		
		return $votos;
	}
}