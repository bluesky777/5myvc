<?php namespace App\Http\Controllers;

use Request;
use DB;


use App\Models\User;
use App\Models\VtAspiracion;
use App\Models\VtVotacion;
use \DateTime;


class VtVotacionesController extends Controller {


	public function getIndex()
	{
		$user = User::fromToken();
		return VtVotacion::where('user_id', $user->id)->get();
	}



	public function postStore()
	{

		$user = User::fromToken();

		try {

			if (Request::input('actual') == 1) {
				$consulta = 'UPDATE vt_votaciones SET actual=0 WHERE actual=1;';
				DB::statement($consulta);
			}
			
			$fecha 			= date("Y-m-d H:i:s");
			$fecha_inicio 	= Request::input('fecha_inicio');
			$fecha_fin 		= Request::input('fecha_fin');


			if ($fecha_inicio == NULL) {
				$fecha_inicio = $fecha;
			}
			if ($fecha_fin == NULL) {
				$fecha_fin = $fecha;
			}

			
			
			$datos = ['user_id'		=>	$user->id,
					'nombre'		=>	Request::input('nombre'),
					'locked'		=>	Request::input('locked', false),
					'actual'		=>	Request::input('actual', false),
					'in_action'		=>	Request::input('in_action', false),
					'fecha_inicio'	=>	$fecha_inicio,
					'fecha_fin'		=>	$fecha_fin,
					'created_at'	=>	$fecha,
					'updated_at'	=>	$fecha,
					];


			$votacion = DB::table('vt_votaciones')->insertGetId($datos);

			$datos['id'] = $votacion;



			$aspiraciones = Request::input('aspiraciones');

			foreach ($aspiraciones as $key => $aspiracion) {
				$asp 				= new VtAspiracion;
				$asp->aspiracion 	= $aspiracion->aspiracion;
				$asp->abrev 		= $aspiracion->abrev;
				$asp->votacion_id 	= $votacion;
				$asp->save();
			}


			return $datos;
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}


	public function getShow($id)
	{
		return VtVotacion::findOrFail($id);
	}
	
	public function getActual()
	{
		$user = User::fromToken();
		return VtVotacion::where('actual', true)->where('user_id', $user->id)->first();
	}

	public function getUnsignedsusers()
	{
		$consulta = 'SELECT u.id, u.username, u.email, u.is_superuser 
					FROM users u 
					where u.id not in (select p.user_id from vt_participantes p)';
		return DB::select(DB::raw($consulta));
	}


	public function putSetLocked()
	{
		$user = User::fromToken();
		$consulta = 'SELECT u.id, u.username, u.email, u.is_superuser 
					FROM users u where u.id not in 
						(select p.user_id 
						from vt_participantes p
						inner join vt_votaciones v on v.id=p.votacion_id and v.actual=true and v.user_id=?)';
		return DB::select($consulta, [$user->id]);
	}

	public function putSetInAction()
	{
		$user = User::fromToken();

		$id = Request::input('id');

		$consulta = 'UPDATE vt_votaciones v SET v.in_action=false WHERE v.user_id=?';
		DB::statement($consulta, [$user->id]);

		$consulta = 'UPDATE vt_votaciones v SET v.in_action=true WHERE v.id=?';
		DB::statement($consulta, [$id]);

		return true;
	}

	public function putSetActual()
	{
		$consulta = 'SELECT u.id, u.username, u.email, u.is_superuser 
					FROM users u where u.id not in (select p.user_id from vt_participantes p)';
		return DB::select(DB::raw($consulta));
	}


	public function update($id)
	{
		$votacion = VtVotacion::findOrFail($id);
		try {
			$votacion->nombre		=	Request::input('nombre');
			$votacion->locked		=	Request::input('locked');
			$votacion->actual		=	Request::input('actual');
			$votacion->in_action	=	Request::input('in_action');
			$votacion->fecha_inicio	=	Request::input('fecha_inicio');
			$votacion->fecha_fin	=	Request::input('fecha_fin');

			$votacion->save();
			return $votacion;
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}


	public function deleteDestroy($id)
	{
		$votaciones = VtVotacion::findOrFail($id);
		$votaciones->delete();

		return $votaciones;
	}

}