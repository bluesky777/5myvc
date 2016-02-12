<?php namespace App\Http\Controllers;

use Request;
use DB;


use App\Models\User;
use App\Models\VtAspiracion;
use App\Models\VtVotacion;


class VtAspiracionesController extends Controller {


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

	public function store()
	{

		try {
			$aspiracion = VtAspiracion::create([
				'aspiracion'	=>	Request::input('aspiracion'),
				'abrev'			=>	Request::input('abrev'),
				'votacion_id'	=>	Request::input('votacion_id')

			]);
			return $aspiracion;
		} catch (Exception $e) {
			return abort(400, 'Datos incorrectos');
			return $e;
		}
	}


	public function update($id)
	{

		$aspiracion = VtAspiracion::findOrFail($id);
		try {
			$aspiracion->fill([
				'aspiracion'=>	Request::input('aspiracion'),
				'abrev'		=>	Request::input('abrev')

			]);

			$aspiracion->save();
			return $aspiracion;
		} catch (Exception $e) {
			return abort(400, 'Datos incorrectos');
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