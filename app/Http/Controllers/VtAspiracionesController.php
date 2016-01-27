<?php namespace App\Http\Controllers;

class VtAspiracionesController extends Controller {

	/**
	 * Display a listing of the resource.
	 * GET /aspiracions
	 *
	 * @return Response
	 */
	public function index()
	{
		$votacion = $this->eventoactual();
		$aspiraciones = VtAspiracion::where('votacion_id', '=', $votacion->id)->get();
		return $aspiraciones;
	}
	public function eventoactual()
	{
		$votacion = VtVotacion::where('actual', '=', true)->first();
		return $votacion;
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /aspiracions/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /aspiracions
	 *
	 * @return Response
	 */
	public function store()
	{
		Eloquent::unguard();
		try {
			$aspiracion = VtAspiracion::create([
				'aspiracion'	=>	Input::get('aspiracion'),
				'abrev'			=>	Input::get('abrev'),
				'votacion_id'	=>	Input::get('votacion_id')

			]);
			return $aspiracion;
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}

	/**
	 * Display the specified resource.
	 * GET /aspiracions/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /aspiracions/{id}/edit
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
	 * PUT /aspiracions/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		Eloquent::unguard();
		$aspiracion = VtAspiracion::findOrFail($id);
		try {
			$aspiracion->fill([
				'aspiracion'=>	Input::get('aspiracion'),
				'abrev'		=>	Input::get('abrev')

			]);

			$aspiracion->save();
			return $aspiracion;
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /aspiracions/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$aspiracion = VtAspiracion::findOrFail($id);
		$aspiracion->delete();

		return $aspiracion;
	}

}