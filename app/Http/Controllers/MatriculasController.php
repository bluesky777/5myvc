<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Matricula;


class MatriculasController extends Controller {




	public function postMatricularuno($alumno_id, $grupo_id)
	{
		return Matricula::matricularUno($alumno_id, $grupo_id);
	}




	public function deleteDestroy($id)
	{
		$matri = Matricula::findOrFail($id);
		$matri->delete();
		return $matri;
	}

}