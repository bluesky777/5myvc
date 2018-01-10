<?php namespace App\Http\Controllers;


use JWTAuth;
use Browser;
use Tymon\JWTAuth\Exceptions\JWTException;

use Illuminate\Http\Request;
//use Request;
use Auth;
use Hash;
use DB;
use Carbon\Carbon;


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

		
		$consulta 	= 'SELECT id, tipo, password FROM users WHERE username=? and deleted_at is null';
		$usuario 	= DB::select($consulta, [ $credentials['username'] ])[0];

		if (Hash::check($credentials['password'], $usuario->password)){

			//$br 		= Browser::detect(); return Browser::isDesktop() . '';
			$entorno 	= 'Desktop';
			$now 		= Carbon::now('America/Bogota');

			if (Browser::isMobile()) {
				$entorno 	= 'Mobile';
			}else if(Browser::isTablet()){
				$entorno 	= 'Tablet';
			}else if(Browser::isBot()){
				$entorno 	= 'Bot';
			}

			// Averiguamos la IP
			$direccion = '';
			if (!empty($_SERVER['HTTP_CLIENT_IP']))
				$direccion = $_SERVER['HTTP_CLIENT_IP'];
			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
				$direccion = $_SERVER['HTTP_X_FORWARDED_FOR'];
			if (!empty($_SERVER['REMOTE_ADDR']))
				$direccion = $_SERVER['REMOTE_ADDR'];


			
			// Alumnos asistentes o matriculados del grupo
			$consulta = 'INSERT INTO historiales(user_id, tipo, ip, browser_name, browser_version, browser_family, browser_engine, entorno, platform_name, platform_family, device_family, device_model, device_grade, updated_at, created_at) 
										VALUES(:user_id, :tipo, :ip, :browser_name, :browser_version, :browser_family, :browser_engine, :entorno, :platform_name, :platform_family, :device_family, :device_model, :device_grade, :updated_at, :created_at)';
			
			$result = DB::insert($consulta, [ ':user_id' => $usuario->id, ':tipo' => $usuario->tipo, ':ip' => $direccion, 
				':browser_name' => Browser::browserName(), ':browser_version' => Browser::browserVersion(), ':browser_family' => Browser::browserFamily(), 
				':browser_engine' => Browser::browserEngine(), ':entorno' => $entorno, ':platform_name' => Browser::browserEngine(), ':platform_family' => Browser::platformFamily(), ':device_family' => Browser::deviceFamily(), ':device_model' => Browser::deviceModel(), ':device_grade' => Browser::mobileGrade(), ':updated_at' => $now, ':created_at' => $now ]);
			
		}

		

		//return ['token' => compact('token')];
		return [ 'el_token' => $token ];

		
	}



	public function putLogout(Request $request){
		$now 		= Carbon::now('America/Bogota');

		$consulta 	= 'UPDATE historiales SET logout_at=? where user_id=? and deleted_at is null order by id desc limit 1';
		DB::update($consulta, [ $now, $request->input('user_id') ])[0];
		
		return 'Deslogueado';
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