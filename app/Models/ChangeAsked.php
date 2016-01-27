<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ChangeAsked extends Model {
	protected $fillable = [];

	protected $table = 'change_asked';

	use SoftDeletes;
	protected $softDelete = true;
}