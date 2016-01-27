<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class VtVotacion extends Model {
	protected $fillable = [];
	protected $table = "vt_votaciones";

	use SoftDeletes;
	protected $softDelete = true;

	public static function actual()
	{
		$votacion = VtVotacion::where('actual', '=', true)->first();
		return $votacion;
	}
}