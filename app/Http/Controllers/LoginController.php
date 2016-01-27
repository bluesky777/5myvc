<?php namespace App\Http\Controllers;


use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Request;
use Auth;
use Hash;
use DB;


use App\Models\User;


class LoginController extends Controller {


	public function postIndex()
	{

		$userTemp = [];
		$token = [];

		try
		{
			$token = JWTAuth::parseToken();


			if ($token){
				$userTemp = User::fromToken();
			}else if ((!(Request::has('username')) && Request::input('username') != ''))  {
				return response()->json(['error' => 'Token expirado'], 401);
			}
		}
		catch(Tymon\JWTAuth\Exceptions\TokenExpiredException $e)
		{
			if (! count(Input::all())) {
				return response()->json(['error' => 'token_expired'], 401);
			}
		}
		catch(JWTException $e){
			// No haremos nada, continuaremos verificando datos.
		}



		$credentials = [
			'username' => Request::input('username'),
			'password' => (string)Request::input('password')
		];
		

		if (! $userTemp) // Si no es válido con token, nos autenticaremos con las credenciales
		{

			if (Auth::attempt($credentials)) {
				$userTemp = Auth::user();

			}else if (Request::has('username') && Request::input('username') != ''){

				$pass = Hash::make((string)Request::input('password'));
				$usuario = User::where('password', '=', $pass)
								->where('username', '=', Request::input('username'))
								->get();

				if ( count( $usuario) > 0) {
					$userTemp = Auth::login($usuario[0]);
				}else{
					$usuario = User::where('password', '=', (string)Request::input('password'))
								->where('username', '=', Request::input('username'))
								->get();
					if ( count( $usuario) > 0) {
						$usuario[0]->password = Hash::make((string)$usuario[0]->password);
						$usuario[0]->save();
						$userTemp = Auth::loginUsingId($usuario[0]->id);
					}else{
						return abort(400, 'Credenciales inválidas.');
					}
				}
			}else{
				return abort(401, 'Por favor ingrese de nuevo.');
			}

			if (!$token){
				if ( ! $token = JWTAuth::attempt($credentials) )
				{
					return abort('401', 'Usuario o contraseña incorrectos para el token.');
				}
			}

			$userTemp = User::fromToken($token);
		}

		//return json_decode(json_encode($user[0]), true);

		return json_decode(json_encode($userTemp), true);
	}

	function default_image_id($sexo)
	{
		if ($sexo == 'F') {
			return 2;
		}else{
			return 1; // ID de la imagen masculina
		}
	}
	function default_image_name($sexo)
	{
		if ($sexo == 'F') {
			return 'default_female.jpg';
		}else{
			return 'default_male.jpg';
		}
	}



}