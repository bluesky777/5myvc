<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Materia;

class MateriasController extends Controller {

	public function getIndex()
	{
		return Materia::all();
	}

	public function postIndex()
	{

		if (Request::input('area')['id']) {
			Request::merge(array('area' => Request::input('area')['id'] ) );
		}

		$materia = new Materia;
		$materia->materia	=	Request::input('materia');
		$materia->alias		=	Request::input('alias');
		$materia->area_id	=	Request::input('area');
		$materia->save();

		return $materia;

	}


	public function putUpdate($id)
	{


		if (Request::input('area_id')) {
			Request::merge(array('area' => Request::input('area_id') ) );
		}else if (Request::input('area')['id']) {
			Request::merge(array('area' => Request::input('area')['id'] ) );
		}

		$materia = Materia::findOrFail($id);
		$materia->materia	=	Request::input('materia');
		$materia->alias		=	Request::input('alias');
		$materia->area_id	=	Request::input('area');


		$materia->save();
		return $materia;
	}


	public function deleteDestroy($id)
	{
		$materia = Materia::findOrFail($id);
		$materia->delete();

		return $materia;
	}

}