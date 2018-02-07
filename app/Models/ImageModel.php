<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use File;


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



	public static function eliminar_imagen_y_enlaces($imagen_id)
	{
		$img 		= ImageModel::findOrFail($imagen_id);
		$filename 	= 'images/perfil/'.$img->nombre;

		if (File::exists($filename)) {
			File::delete($filename);
			$img->delete();
		}else{
			return 'No existe';
		}


		// Elimino cualquier referencia que otros tengan a esa imagen borrada.
		$alumnos = Alumno::where('foto_id', $imagen_id)->get();
		foreach ($alumnos as $alum) {
			$alum->foto_id = null;
			$alum->save();
		}
		
		$profesores = Profesor::where('foto_id', $imagen_id)->get();
		foreach ($profesores as $prof) {
			$prof->foto_id = null;
			$prof->save();
		}
		$profesores = Profesor::where('firma_id', $imagen_id)->get();
		foreach ($profesores as $prof) {
			$prof->firma_id = null;
			$prof->save();
		}
		
		$acudientes = Acudiente::where('foto_id', $imagen_id)->get();
		foreach ($acudientes as $acud) {
			$acud->foto_id = null;
			$acud->save();
		}
		$users = User::where('imagen_id', $imagen_id)->get();
		foreach ($users as $user) {
			$user->imagen_id = null;
			$user->save();
		}
		$years = Year::where('logo_id', $imagen_id)->get();
		foreach ($years as $year) {
			$year->logo_id = null;
			$year->save();
		}
		
		
	}


}