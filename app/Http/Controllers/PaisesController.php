<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Pais;


class PaisesController extends Controller {


	public function getIndex()
	{
		$consulta = 'SELECT * FROM paises where deleted_at is null';
		return DB::select($consulta);
		//return Pais::all();
	}


	public function store()
	{
		
		try {
			$pais 			= new Pais;
			$pais->pais		=	Request::input('pais');
			$pais->abrev	=	Request::input('abrev');
			$pais->save();

			return $pais;

		} catch (Exception $e) {
			return $e;
		}
	}




	public function update($id)
	{
		$pais = Pais::findOrFail($id);
		try {

			$pais->pais		=	Request::input('pais');
			$pais->abrev	=	Request::input('abrev');
			$pais->save();

			$pais->save();
			
		} catch (Exception $e) {
			return $e;
		}
	}

	public function destroy($id)
	{
		$pais = Pais::findOrFail($id);
		$pais->delete();

		return $pais;
	}

}