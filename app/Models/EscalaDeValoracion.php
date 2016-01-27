<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class EscalaDeValoracion extends Model {
	protected $fillable = [];

	protected $table = 'escalas_de_valoracion';

	use SoftDeletes;
	protected $softDelete = true;
}