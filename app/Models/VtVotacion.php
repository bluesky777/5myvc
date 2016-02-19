<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


use DB;


class VtVotacion extends Model {
	protected $fillable = [];
	protected $table = "vt_votaciones";

	use SoftDeletes;
	protected $softDelete = true;


	public static function actual($user)
	{
		return VtVotacion::where('actual', true)
					->where('user_id', $user->id)
					->where('year_id', $user->year_id)
					->first();
	}

	public function actualInAction($user)
	{
		return VtVotacion::where('actual', true)
					->where('user_id', $user->id)
					->where('in_action', true)
					->where('year_id', $user->year_id)
					->first();
	}

	public static function actualesInscrito($user, $in_action=true)
	{
		
		if ($in_action) {
			$consulta = 'SELECT p.id as participante_id, p.user_id, p.votacion_id, p.locked, 
						p.intentos, v.created_by, 
						v.id as votacion_id, v.year_id, v.nombre, v.locked as locked_votacion,
						v.actual, v.in_action, v.can_see_results, v.fecha_inicio, v.fecha_fin,
						v.created_at
					FROM vt_participantes p
					inner join vt_votaciones v on v.id=p.votacion_id and p.deleted_at is null 
						and v.in_action=true and v.year_id=? 
					where p.user_id=? and p.deleted_at is null';

		}else{
			$consulta = 'SELECT p.id as participante_id, p.user_id, p.votacion_id, p.locked, 
						p.intentos, v.created_by, 
						v.id as votacion_id, v.year_id, v.nombre, v.locked as locked_votacion,
						v.actual, v.in_action, v.can_see_results, v.fecha_inicio, v.fecha_fin,
						v.created_at
					FROM vt_participantes p
					inner join vt_votaciones v on v.id=p.votacion_id and p.deleted_at is null 
						and v.year_id=? 
					where p.user_id=? and p.deleted_at is null';

		}
		
		$votaciones = DB::select($consulta, [$user->year_id, $user->user_id]);
		return $votaciones;
	}


	public static function verificarVotosCompletos($votacion_id, $particip_id)
	{
		$aspiraciones = VtAspiracion::where('votacion_id', '=', $votacion_id)->get();
		$cons = 'SELECT vv.participante_id, vv.candidato_id, vp.votacion_id, vv.created_at
				FROM vt_votos vv
				inner join vt_participantes vp on vp.id=vv.participante_id and vv.participante_id=:participante_id
				inner join vt_candidatos vc on vc.id=vv.candidato_id
				inner join vt_aspiraciones va on va.id=vc.aspiracion_id and va.votacion_id=:votacion_id';

		$votosVotados = DB::select(DB::raw($cons), array('votacion_id' => $votacion_id, 'participante_id' => $particip_id));

		$cantVotados = count($votosVotados);

		if ($cantVotados < count($aspiraciones)) {
			$completo = false;
		}else{
			$completo = true;
		}
		return $completo;
	}



}