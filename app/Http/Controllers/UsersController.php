<?php namespace App\Http\Controllers;

class UsersController extends Controller {


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

	public function create()
	{
		//
	}

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

	public function show($id)
	{
		//
	}

	public function edit($id)
	{
		//
	}

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

	public function destroy($id)
	{
		$aspiracion = VtAspiracion::findOrFail($id);
		$aspiracion->delete();

		return $aspiracion;
	}

}