<?php namespace App\Http\Controllers;


use JWTAuth;
use Browser;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Mail;

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





	public function postVerPass(Request $request){
		$now 			= Carbon::now('America/Bogota');
		$hora 			= Carbon::now('America/Bogota')->subHour(); 
		$destinatario 	= $request->input('email');
		$numero 		= rand(100000, 9999999999999999);
		
		$username 		= '';

		$consulta 	= 'INSERT INTO password_reminders(email, token, created_at) VALUES(?,?,?)';
		DB::insert($consulta, [ $destinatario, $numero, $now ])[0];


		$consulta 	= 'SELECT * FROM users WHERE email = ? and deleted_at is null and is_active=1';
		$persona 	= DB::select($consulta, [ $destinatario ]);

		if (count($persona) > 0) {
			$persona 	= $persona[0];
			$username 	= $persona->username;
		}else{

			$consulta 	= 'SELECT u.username FROM alumnos a INNER JOIN users u ON u.id=a.user_id and u.deleted_at is null and u.is_active=1 WHERE u.email = ? and a.deleted_at is null';
			$persona 	= DB::select($consulta, [ $destinatario ]);

			if (count($persona) > 0) {
				$persona 	= $persona[0];
				$username 	= $persona->username;
			}else{

				$consulta 	= 'SELECT u.username FROM profesores p INNER JOIN users u ON u.id=p.user_id and u.deleted_at is null and u.is_active=1 WHERE u.email = ? and p.deleted_at is null';
				$persona 	= DB::select($consulta, [ $destinatario ]);

				if (count($persona) > 0) {
					$persona 	= $persona[0];
					$username 	= $persona->username;
				}else{
					
					$consulta 	= 'SELECT u.username FROM acudientes a INNER JOIN users u ON u.id=a.user_id and u.deleted_at is null and u.is_active=1 WHERE u.email = ? and a.deleted_at is null';
					$persona 	= DB::select($consulta, [ $destinatario ]);

					if (count($persona) > 0) {
						$persona 	= $persona[0];
						$username 	= $persona->username;
					}else{
						return 'No existe';
					}

				}

			}

		}

		$ruta 		= $request->input('ruta') . '#!/reset-password/'.$numero.'/'.$username;

        $asunto = "Ver contraseña Mi Colegio Virtual";
        $cuerpo = '
        <style>
			/* Shrink Wrap Layout Pattern CSS */
			@media only screen and (max-width: 599px) {
				td[class="hero"] img {
					width: 100%;
					height: auto !important;
				}
				td[class="pattern"] td{
					width: 100%;
				}
			}
		</style>

		<table cellpadding="0" cellspacing="0">
			<tr>
				<td class="pattern" width="600">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td class="hero">
								<img src="http://lalvirtual.com/up/images/Logo_MyVc_Header.gif" alt="Mi Colegio Virtual" style="display: block; border: 0;" />
							</td>
						</tr>
						<tr>
							<td align="left" style="font-family: arial,sans-serif; color: #333;">
								<h1>My Virtual College</h1>
							</td>
						</tr>
						<tr>
							<td align="left" style="font-family: arial,sans-serif; font-size: 14px; line-height: 20px !important; color: #666; padding-bottom: 20px;">
								Has solicitado resetear tu contraseña. Si es así, presiona botón de abajo. De lo contrario, puedes ignorar este mensaje. Este link sólo será válido durante una hora. Tu usuario es <b>'.$username.'</b>
							</td>
						</tr>
						<tr>
							<td align="left">
								<a href="'.$ruta.'"><img src="http://placehold.it/200x50/333&text=Resetear" alt="Resetear" style="display: block; border: 0;" /></a>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
        ';
        
        //para el envío en formato HTML
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";

        //dirección del remitente
        
        $headers .= "From: MiColegioVirtual <josethmaster@lalvirtual.com>\r\n";

        //ruta del mensaje desde origen a destino
        $headers .= "Return-path: josethmaster@lalvirtual.com\r\n";

		mail($destinatario,$asunto,$cuerpo,$headers);
		
		
		
		return 'Enviado';
	}




	public function putResetPassword(Request $request){
		$now 			= Carbon::now('America/Bogota');
		$hora 			= Carbon::now('America/Bogota')->subHour(); 

		$numero 		= $request->input('numero');
		$pass1 			= Hash::make($request->input('password1'));
		$username 		= $request->input('username');
	


		$consulta 	= 'SELECT * FROM password_reminders WHERE token=? and created_at > ?';
		$reminder 	= DB::select($consulta, [ $numero, $hora ]);

		if (count($reminder) > 0) {
			$reminder = $reminder[0];

			$consulta 	= 'UPDATE users SET password=? WHERE username = ?';
			DB::update($consulta, [ $pass1, $username ]);


			$consulta 	= 'DELETE FROM password_reminders WHERE token=?';
			DB::delete($consulta, [ $numero ]);


		} else {
			return 'Token inválido';
		}
		


		return 'Reseteado';
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