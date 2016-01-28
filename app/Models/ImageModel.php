<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;


class ImageModel extends Model {
	protected $fillable = [];

	protected $table = 'images';

	use SoftDeletes;
	protected $softDelete = true;



	public static function DatosImagen($imagen_id, $user_id)
	{
		$datos_imagen = null;


		$consulta = 'SELECT a.nombres, i.nombre, a.apellidos, a.sexo, "alumno" as usuario, i.id  FROM images i 
				inner join alumnos a on a.foto_id=i.id and i.id=:imagen_id1
				UNION 
				SELECT p.nombres, i.nombre, p.apellidos, p.sexo, "profesor", i.id FROM images i 
				inner join profesores p on p.foto_id=i.id  and i.id=:imagen_id2
				UNION 
				SELECT a.nombres, i.nombre, a.apellidos, a.sexo, "acudiente", i.id FROM images i 
				inner join acudientes a on a.foto_id=i.id  and i.id=:imagen_id3';

		$oficiales = DB::select(DB::raw($consulta), array(
					':imagen_id1'	=> $imagen_id,
					':imagen_id2'	=> $imagen_id,
					':imagen_id3'	=> $imagen_id,
				));


		$consulta = 'SELECT u.username, i.nombre, u.sexo, i.id FROM images i 
				inner join users u on u.imagen_id=i.id  and i.id=:imagen_id';

		$de_usuario = DB::select(DB::raw($consulta), array(
					':imagen_id'	=> $imagen_id
				));

		$datos_imagen = array('oficiales' => $oficiales, 'de_usuario' => $de_usuario);

		return $datos_imagen;
	}


}