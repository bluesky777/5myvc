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
use App\Models\ChangeAsked;


class ImagesController extends Controller {

	public function getIndex()
	{
		$user = User::fromToken();
		

		if ($user->tipo == "Alumno" and $user->tipo == "Acudiente") {
			return ImageModel::where('user_id', $user->user_id)->get();
		}

		$imagenes_privadas = ImageModel::where('user_id', $user->user_id)
								->whereNull('publica')
								->get();

		$imagenes_publicas = ImageModel::where('user_id', $user->user_id)
								->where('publica', true)
								->get();

		return array('imagenes_privadas' => $imagenes_privadas, 'imagenes_publicas' => $imagenes_publicas);
	}


	public function getDatosImagen()
	{
		//$user = User::fromToken();
		$user_id = Request::input('user_id');
		$imagen_id = Request::input('imagen_id');
		

		$datos_imagen = ImageModel::DatosImagen($imagen_id, $user_id);

		return $datos_imagen;
	}




	public function postStore()
	{
		$user = User::fromToken();

		$folder = 'images/perfil/';

		$newImg = $this->guardar_imagen($user);

		try {
			
			$img = Image::make($folder . $newImg->nombre);
			$img->fit(300);
			//$img->resize(300, null, function ($constraint) {
			//	$constraint->aspectRatio();
			//});
			$img->save();
		} catch (Exception $e) {
			
		}

		return $newImg;
	}



	public function postStoreIntacta()
	{
		$user = User::fromToken();
		
		$newImg = $this->guardar_imagen($user);
		$newImg->publica = true;
		$newImg->save();

		return $newImg;
	}

	public function guardar_imagen($user)
	{
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
		//mientras el nombre exista iteramos y aumentamos i
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


	public function putPublicarImagen($imagen_id)
	{
		$imagen = ImageModel::findOrFail($imagen_id);
		$imagen->publica = true;
		$imagen->save();

		return $imagen->nombre;
	}

	public function putPrivatizarImagen($imagen_id)
	{
		$years = Year::where('logo_id', $imagen_id)->get();
		
		if (count($years) > 0) {
			return ['imagen' => array('is_logo_of_year' => $years[0]->year)];
		}


		$imagen = ImageModel::findOrFail($imagen_id);
		$imagen->publica = null;
		$imagen->save();

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
		$img_id = Request::input('foto_id');
		$persona = new stdClass();
		
		$alumno = Alumno::where('user_id', $user->id)->first();

		if ($alumno) {
			
			// No cambiamos la imagen, solo la solicitamos.
			$persona = $alumno;

			$already = ChangeAsked::where('asked_by_user_id', $user->id)
								->where('oficial_image_id', $img_id)
								->whereNull('rechazado_at')
								->whereNull('accepted_at')
								->first();

			if ($already) {
				return 'En espera';
			}

			$pedido = new ChangeAsked;
			$pedido->asked_by_user_id = $user->id;
			$pedido->oficial_image_id = $img_id;
			$pedido->save();
			return $pedido;

		}else{

			$profesor = Profesor::where('user_id', $user->id)->first();
			if ($profesor) {
				$persona = $profesor;
			}else{
				$acudiente = Acudiente::where('user_id', $user->id)->first();
				if ($acudiente) {
					$persona = $acudiente;
				}else{
					App::abort(400, 'Usuario no tiene foto oficial.');
				}
			}


			$persona->foto_id = $img_id;
			$persona->save();
			return $persona;

		}

	}


	public function deleteDestroy($id)
	{
		$img = ImageModel::findOrFail($id);
		
		$filename = 'images/perfil/'.$img->nombre;


		// Debería crear un código que impida borrar si la imagen es usada.


		if (File::exists($filename)) {
			File::delete($filename);
			$img->delete();
		}else{
			return 'No se encuentra la imagen a eliminar. '.$img->nombre;
		}


		// Elimino cualquier referencia que otros tengan a esa imagen borrada.
		$alumnos = Alumno::where('foto_id', $id)->get();
		foreach ($alumnos as $alum) {
			$alum->foto_id = null;
			$alum->save();
		}
		$profesores = Profesor::where('foto_id', $id)->get();
		foreach ($profesores as $prof) {
			$prof->foto_id = null;
			$prof->save();
		}
		$acudientes = Acudiente::where('foto_id', $id)->get();
		foreach ($acudientes as $acud) {
			$acud->foto_id = null;
			$acud->save();
		}
		$users = User::where('imagen_id', $id)->get();
		foreach ($users as $user) {
			$user->imagen_id = null;
			$user->save();
		}
		$years = Year::where('logo_id', $id)->get();
		foreach ($years as $year) {
			$year->logo_id = null;
			$year->save();
		}

		
		return $img;
	}

}