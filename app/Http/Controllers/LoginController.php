<?php namespace App\Http\Controllers;


use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Illuminate\Http\Request;
//use Request;
use Auth;
use Hash;
use DB;


use App\Models\User;
use App\Models\VtVotacion;


class LoginController extends Controller {


	public function postIndex(Request $request)
	{

		$user = [];
		$token = [];

		try
		{
			$token = JWTAuth::parseToken();

			if ($token){
				$user = User::fromToken(false, $request);
			}else if ((!($request->has('username')) && $request->input('username') != ''))  {
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




		// Ahora verificamos si está inscrito en alguna votación
		$votaciones 		= VtVotacion::actualesInscrito($user, true);
		$votacionesResult 	= [];

		$cantVot = count($votaciones);

		if ($cantVot > 0) {
			for($i=0; $i<$cantVot; $i++) {
				$completos = VtVotacion::verificarVotosCompletos($votaciones[$i]->votacion_id, $votaciones[$i]->participante_id);
				if (!$completos) {
					array_push($votacionesResult, $votaciones[$i]);
				}
			}

			$cantVot = count($votacionesResult);
			if ($cantVot > 0) {
				$user->votaciones = $votacionesResult;
			}
			
		}

		return json_decode(json_encode($user), true);

		
		
		/*
		$userTemp = [];
		$token = [];

		try
		{
			$token = JWTAuth::parseToken();

			if ($token){
				$userTemp = User::fromToken(false, $request);
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

				$usuario = User::where('password', $pass)
								->where('username', Request::input('username'))
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



			// Ahora verificamos si está inscrito en alguna votación
			$votaciones = VtVotacion::actualesInscrito($userTemp, true);
			$votacionesResult = [];

			$cantVot = count($votaciones);

			if ($cantVot > 0) {
				for($i=0; $i<$cantVot; $i++) {
					$completos = VtVotacion::verificarVotosCompletos($votaciones[$i]->votacion_id, $votaciones[$i]->participante_id);
					if (!$completos) {
						array_push($votacionesResult, $votaciones[$i]);
					}
				}

				$cantVot = count($votacionesResult);
				if ($cantVot > 0) {
					$userTemp->votaciones = $votacionesResult;
				}
				
			}



		}

		//return json_decode(json_encode($user[0]), true);

		return json_decode(json_encode($userTemp), true);
		*/

		
	}

	public function postCredentials(Request $request)
	{

		$user = [];
		$token = [];

		// grab credentials from the request
		
		$credentials = [
			'username' => $request->input('username'),
			'password' => (string)$request->input('password')
		];


		try {
			// attempt to verify the credentials and create a token for the user
			if (! $token = app('auth')->attempt($credentials)) {
				return response()->json(['error' => 'invalid_credentials'], 400);
			}

		} catch (JWTException $e) {
			return response()->json(['error' => 'could_not_create_token'], 500);
		}
		//return ['token' => compact('token')];
		return [ 'el_token' => $token ];

		
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
			return 'default_female.png';
		}else{
			return 'default_male.png';
		}
	}



}