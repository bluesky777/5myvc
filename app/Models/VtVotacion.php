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

}