<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



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

}