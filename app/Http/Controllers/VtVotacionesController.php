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
		
		$votaciones = VtVotacion::where('user_id', $user->id)
							->where('year_id', $user->year_id)->get();

		for($i=0; $i<count($votaciones); $i++){
			$aspiraciones = VtAspiracion::where('votacion_id', $votaciones[$i]->id)->get();
			$votaciones[$i]->aspiraciones = $aspiraciones;
		}

		return $votaciones;
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

			for ($i=0; $i < count($aspiraciones); $i++) {
				$asp 				= new VtAspiracion;
				$asp->aspiracion 	= $aspiraciones[$i]['aspiracion'];
				$asp->abrev 		= $aspiraciones[$i]['abrev'];
				$asp->votacion_id 	= $votacion;
				$asp->save();

				$aspiraciones[$i]['id'] = $asp->id;
			}

			$datos['aspiraciones'] = $aspiraciones;

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
		return VtVotacion::actual($user);
	}

	public function getActualInAction()
	{
		$user = User::fromToken();
		return VtVotacion::actualInAction($user);
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
		$id = Request::input('id');
		$locked = Request::input('locked', true);

		$vot = VtVotacion::where('id', $id)->update(['locked' => $locked]);
		return 'Cambiado';
	}


	public function putSetInAction()
	{
		$user = User::fromToken();
		$id = Request::input('id');
		$in_action = Request::input('in_action', false);

		if ($in_action) {
			
			$consulta = 'UPDATE vt_votaciones v SET v.in_action=false 
						WHERE v.id<>? and v.user_id=? 
							and v.year_id=? and v.in_action=true AND v.deleted_at is null';

			DB::statement($consulta, [$id, $user->id, $user->year_id]);

			
			$consulta = 'UPDATE vt_votaciones v SET v.in_action=true WHERE v.id=?';
			$vot = DB::statement($consulta, [$id]);

			return 'Cambiado true';

		}else{

			$consulta = 'UPDATE vt_votaciones v SET v.in_action=false WHERE v.id=?';
			$vot = DB::statement($consulta, [$id]);
			return 'Cambiado false';

		}
	}


	public function putSetActual()
	{
		$user = User::fromToken();
		$id = Request::input('id');
		$actual = Request::input('actual', true);

		if ($actual) {
			
			$consulta = 'UPDATE vt_votaciones v SET v.actual=false 
						WHERE v.id<>? and v.user_id=? 
							and v.year_id=? and v.actual=true AND v.deleted_at is null';

			DB::statement($consulta, [$id, $user->id, $user->year_id]);

			
			$consulta = 'UPDATE vt_votaciones v SET v.actual=true WHERE v.id=?';
			$vot = DB::statement($consulta, [$id]);

			return 'Cambiado true';

		}else{

			$consulta = 'UPDATE vt_votaciones v SET v.actual=false WHERE v.id=?';
			$vot = DB::statement($consulta, [$id]);
			return 'Cambiado false';

		}
		
	}


	public function putUpdate($id)
	{
		$votacion = VtVotacion::findOrFail($id);
		try {
			$votacion->nombre		=	Request::input('nombre', $votacion->nombre);
			$votacion->locked		=	Request::input('locked', $votacion->locked);
			$votacion->actual		=	Request::input('actual', $votacion->actual);
			$votacion->in_action	=	Request::input('in_action', $votacion->in_action);
			$votacion->fecha_inicio	=	Request::input('fecha_inicio', $votacion->fecha_inicio);
			$votacion->fecha_fin	=	Request::input('fecha_fin', $votacion->fecha_fin);

			$votacion->save();
			return $votacion;
		} catch (Exception $e) {
			return abort(400, 'Datos incorrectos');
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