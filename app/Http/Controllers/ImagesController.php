<?php namespace App\Http\Controllers;

use Request;
use DB;
use File;
use Image;
use \stdClass;

use App\Models\User;
use App\Models\ImageModel;
use App\Models\Year;
use App\Models\Alumno;
use App\Models\Profesor;
use App\Models\Acudiente;


class ImagesController extends Controller {

	public function getIndex()
	{
		$user = User::fromToken();
		return ImageModel::where('user_id', '=', $user->user_id)->get();
	}


	public function postStore()
	{
		$user = User::fromToken();
		$folderName = 'user_'.$user->user_id;
		$folder = 'images/perfil/'.$folderName;

		if (!File::exists($folder)) {
			File::makeDirectory($folder, $mode = 0777, true, true);
		}

		$file = Request::file("file");
		//separamos el nombre de la img y la extensión
		$info = explode(".", $file->getClientOriginalName());
		//asignamos de nuevo el nombre de la imagen completo
		$miImg = $file->getClientOriginalName();

		//return Request::file('file')->getMimeType(); // Puedo borrarlo
		//mientras el archivo exista iteramos y aumentamos i
		$i = 0;
		while(file_exists($folder.'/'. $miImg)){
			$i++;
			$miImg = $info[0]."(".$i.")".".".$info[1];              
		}

		//guardamos la imagen con otro nombre ej foto(1).jpg || foto(2).jpg etc
		$file->move($folder, $miImg);
		
		$newImg = new ImageModel;
		$newImg->nombre = $folderName.'/'.$miImg;
		$newImg->user_id = $user->user_id;
		$newImg->save();



		
		try {
			
			$img = Image::make($folder .'/'.$miImg);
			$img->fit(300);
			//$img->resize(300, null, function ($constraint) {
			//	$constraint->aspectRatio();
			//});
			$img->save();
		} catch (Exception $e) {
			
		}

		return $newImg;
	}


	public function putRotarimagen($imagen_id)
	{
		$imagen = ImageModel::findOrFail($imagen_id);

		$folderName = $imagen->nombre;
		$img_dir = 'images/perfil/'.$folderName;

		$img = Image::make($img_dir);

		$img->rotate(-90);

		$img->save();

		return $imagen->nombre;
	}


	public function putCambiarimagenperfil($id)
	{
		$user = User::findOrFail($id);
		$user->imagen_id = Request::input('imagen_id');
		$user->save();
		return $user;
	}


	public function putCambiarlogocolegio()
	{
		$user = User::fromToken();

		$year = Year::findOrFail($user->year_id);
		$year->logo_id = Request::input('logo_id');
		$year->save();
		return $year;
	}

	public function putCambiarimagenoficial($id)
	{
		$user = User::findOrFail($id);
		$persona = new stdClass();
		
		$alumno = Alumno::where('user_id', '=', $user->id)->first();

		if ($alumno) {
			$persona = $alumno;
		}else{

			$profesor = Profesor::where('user_id', '=', $user->id)->first();
			if ($profesor) {
				$persona = $profesor;
			}else{
				$acudiente = Acudiente::where('user_id', '=', $user->id)->first();
				if ($acudiente) {
					$persona = $acudiente;
				}else{
					App::abort(400, 'Usuario no tiene foto oficial.');
				}
			}
		}

		$persona->foto_id = Request::input('foto_id');
		$persona->save();
		return $persona;
	}


	public function deleteDestroy($id)
	{
		$img = ImageModel::findOrFail($id);
		
		$filename = 'images/perfil/'.$img->nombre;

		if (File::exists($filename)) {
			File::delete($filename);
		}else{
			return 'No se encuentra la imagen a eliminar.'.$img->nombre;
		}

		$alumnos = Alumno::where('foto_id', '=', $id)->get();
		foreach ($alumnos as $alum) {
			$alum->foto_id = null;
			$alum->save();
		}
		$profesores = Profesor::where('foto_id', '=', $id)->get();
		foreach ($profesores as $prof) {
			$prof->foto_id = null;
			$prof->save();
		}
		$acudientes = Acudiente::where('foto_id', '=', $id)->get();
		foreach ($acudientes as $acud) {
			$acud->foto_id = null;
			$acud->save();
		}
		$users = User::where('imagen_id', '=', $id)->get();
		foreach ($users as $user) {
			$user->imagen_id = null;
			$user->save();
		}

		$img->delete();
		return $img;
	}

}