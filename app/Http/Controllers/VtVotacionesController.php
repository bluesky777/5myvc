<?php namespace App\Http\Controllers;

class VtVotacionesController extends Controller {

	/**
	 * Display a listing of the resource.
	 * GET /votacions
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return VtVotacion::all();
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /votacions/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /votacions
	 *
	 * @return Response
	 */
	public function postStore()
	{
		Eloquent::unguard();
		try {

			if (Input::get('actual') == 1) {
				$consulta = 'UPDATE vt_votaciones SET actual=0 WHERE actual=1;';
				DB::select(DB::raw($consulta));
			}

			$votaciones = VtVotacion::create([
				'nombre'		=>	Input::get('nombre'),
				'locked'		=>	Input::get('locked', false),
				'actual'		=>	Input::get('actual', false),
				'in_action'		=>	Input::get('in_action', false),
				'fecha_inicio'	=>	Input::get('fecha_inicio'),
				'fecha_fin'		=>	Input::get('fecha_fin'),

			]);
			return $votaciones;
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}

	/**
	 * Display the specified resource.
	 * GET /votacions/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		return VtVotacion::findOrFail($id);
	}
	
	public function getActual()
	{
		return VtVotacion::where('actual', '=', true)->first();
	}

	public function getUnsignedsusers()
	{
		$consulta = 'SELECT u.id, u.username, u.email, u.is_superuser 
					FROM users u where u.id not in (select p.user_id from vt_participantes p)';
		return DB::select(DB::raw($consulta));
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /votacions/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /votacions/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$votacion = VtVotacion::findOrFail($id);
		try {
			$votacion->nombre		=	Input::get('nombre');
			$votacion->locked		=	Input::get('locked');
			$votacion->actual		=	Input::get('actual');
			$votacion->in_action	=	Input::get('in_action');
			$votacion->fecha_inicio	=	Input::get('fecha_inicio');
			$votacion->fecha_fin	=	Input::get('fecha_fin');

			$votacion->save();
			return $votacion;
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /votacions/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function deleteDestroy($id)
	{
		$votaciones = VtVotacion::findOrFail($id);
		$votaciones->delete();

		return $votaciones;
	}

}