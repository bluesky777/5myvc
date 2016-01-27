<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\NivelEducativo;


class NivelesEducativosController extends Controller {

	public function index()
	{
		return NivelEducativo::orderBy("orden")->get();
	}


	public function store()
	{
		try {
			$nivel = new NivelEducativo;
			$nivel->nombre	=	Request::input('nombre');
			$nivel->abrev	=	Request::input('abrev');
			$nivel->orden	=	Request::input('orden');
			$nivel->save();

			return $nivel;
		} catch (Exception $e) {
			return abort('400', 'Datos incorrectos');
			return $e;
		}
	}


	public function show($id)
	{
		return NivelEducativo::findOrFail($id);
	}


	public function update($id)
	{
		$nivel = NivelEducativo::findOrFail($id);
		try {
			$nivel->nombre	=	Request::input('nombre');
			$nivel->abrev	=	Request::input('abrev');
			$nivel->orden	=	Request::input('orden');

			$nivel->save();
			return $nivel;
		} catch (Exception $e) {
			return abort('400', 'Datos incorrectos');
			return $e;
		}
	}


	public function destroy($id)
	{
		$nivel = NivelEducativo::findOrFail($id);
		$nivel->delete();

		return $nivel;
	}

}