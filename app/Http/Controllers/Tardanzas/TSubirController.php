<?php namespace App\Http\Controllers\Tardanzas;


use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;

use Request;
use Auth;
use Hash;
use DB;

use App\Models\Debugging;
use App\Models\User;
use App\Models\Ausencia;

use Carbon\Carbon;
use \DateTime;


class TSubirController extends Controller {

	public function postIndex()
	{

		$credentials = [
			'username' => Request::input('username'),
			'password' => (string)Request::input('password')
		];

		$ausencias_to_create = Request::input('ausencias_to_create');
		
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



		$consulta = '';

		if (!($userTemp->tipo == 'Profesor' || $userTemp->is_superuser)) {  // Alumno, Profesor, Acudiente, Usuario.
			return abort(400, 'No tienes permiso');
		}


		foreach ($ausencias_to_create as $key => $ausencia_to) {

			if ($ausencia_to['uploaded'] == 'to_delete') {
				$aus = Ausencia::find($ausencia_to['id']);
				$aus->uploaded = 'deleted';
				$aus->save();
				$aus->delete();

			}else{

				$dt = Carbon::now()->format('Y-m-d G:H:i');

				$consulta = 'INSERT INTO ausencias
								(alumno_id, asignatura_id, cantidad_ausencia, cantidad_tardanza, entrada, fecha_hora, periodo_id, uploaded, created_by, created_at, updated_at)
							VALUES (:alumno_id, :asignatura_id, :cantidad_ausencia, :cantidad_tardanza, :entrada, :fecha_hora, :periodo_id, :uploaded, :created_by, :created_at, :updated_at)';


				$ausenc = DB::select($consulta, [
					':alumno_id'			=> $ausencia_to['alumno_id'], 
					':asignatura_id'		=> $ausencia_to['asignatura_id'],
					':cantidad_ausencia'	=> $ausencia_to['cantidad_ausencia'], 
					':cantidad_tardanza'	=> $ausencia_to['cantidad_tardanza'], 
					':entrada'				=> $ausencia_to['entrada'], 
					':fecha_hora'			=> $ausencia_to['fecha_hora'], 
					':periodo_id'			=> $ausencia_to['periodo_id'],
					':uploaded'				=> 'created',
					':created_by'			=> $ausencia_to['created_by'],
					':created_at'			=> $dt,
					':updated_at'			=> $dt,
				]);

			}
			

		}
		
	

		return json_decode(json_encode(['result' => 'Datos subidos']), true);
	}




}