<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class ImageModel extends Model {
	protected $fillable = [];

	protected $table = 'images';

	use SoftDeletes;
	protected $softDelete = true;
}