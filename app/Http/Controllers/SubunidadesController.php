<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Unidad;
use App\Models\Subunidad;


class SubunidadesController extends Controller {

	

	public function postIndex()
	{
		$cant = Subunidad::where('unidad_id', Request::input('unidad_id'))->count();

		$subunidad = new Subunidad;

		$nota_def = Request::input('nota_default');
		
		if (!$nota_def or $nota_def =='' or $nota_def < 0) {
			$nota_def = 0;
		}

		$subunidad->definicion		= Request::input('definicion');
		$subunidad->porcentaje		= Request::input('porcentaje');
		$subunidad->orden			= Request::input('orden', 0);
		$subunidad->unidad_id		= Request::input('unidad_id');
		$subunidad->nota_default	= $nota_def;
		$subunidad->orden			= $cant;

		$subunidad->save();

		return $subunidad;
	}





	public function putSubirSubunidad()
	{

		$subunidad2 = Subunidad::where('unidad_id', Request::input('unidad_id'))
							->where('orden', Request::input('indice_new'))
							->orderBy('id', 'desc')->first();
		if ($subunidad2) {
			$subunidad2->orden = Request::input('indice_new') + 1;
			$subunidad2->save();
		}
		
		

		$subunidad 				= Subunidad::find(Request::input('subunidad_id'));
		$subunidad->orden 		= Request::input('indice_new');
		$subunidad->save();


		return $subunidad;
	}


	public function putBajarSubunidad()
	{

		$subunidad2 = Subunidad::where('unidad_id', Request::input('unidad_id'))
							->where('orden', Request::input('indice_new'))->first();

		if ($subunidad2) {
			$subunidad2->orden = Request::input('indice_new') - 1;
			$subunidad2->save();
		}

		$subunidad2->orden = Request::input('indice_new') - 1;
		$subunidad2->save();


		$subunidad 				= Subunidad::find(Request::input('subunidad_id'));
		$subunidad->orden 		= Request::input('indice_new');
		$subunidad->save();


		return $subunidad;
	}





	public function putUpdate($id)
	{
		$subunidad = Subunidad::findOrFail($id);

		$nota_def = Request::input('nota_default');

		if (!$nota_def or $nota_def =='' or $nota_def < 0) {
			$nota_def = 0;
		}

		$subunidad->definicion		= Request::input('definicion');
		$subunidad->porcentaje		= Request::input('porcentaje');
		$subunidad->nota_default	= $nota_def;

		if ( Request::has('orden') ) {
			$subunidad->orden	= Request::input('orden');
		}
		

		$subunidad->save();

		return $subunidad;
	}




	public function deleteDestroy($id)
	{
		$user = User::fromToken();
		$subunidad = Subunidad::find($id);
		//$queries = DB::getQueryLog();
		//$last_query = end($queries);
		//return $last_query;

		if ($subunidad) {
			$subunidad->delete();
		}else{
			return App::abort(400, 'Subunidad no existe o está en Papelera.');
		}
		return $subunidad;
	
	}	

	public function deleteForcedelete($id)
	{
		$user = User::fromToken();
		$subunidad = Subunidad::onlyTrashed()->findOrFail($id);
		
		if ($unidad) {
			$subunidad->forceDelete();
		}else{
			return App::abort(400, 'Subunidad no encontrada en la Papelera.');
		}
		return $subunidad;
	
	}

	public function putRestore($id)
	{
		$user = User::fromToken();
		$subunidad = Subunidad::onlyTrashed()->findOrFail($id);

		if ($subunidad) {
			$subunidad->restore();
		}else{
			return App::abort(400, 'Subunidad no encontrada en la Papelera.');
		}
		return $subunidad;
	}


	public function getTrashed()
	{
		$user = User::fromToken();
		$consulta = 'SELECT m2.matricula_id, a.id as alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
				a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
				m2.year_id, m2.grupo_id, m2.nombregrupo, m2.abrevgrupo, IFNULL(m2.actual, -1) as currentyear,
				u.username, u.is_superuser, u.is_active
			FROM alumnos a left join 
				(select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 0 as actual
				from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=1
				and m.alumno_id NOT IN 
					(select m.alumno_id
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=2)
					union
					select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 1 AS actual
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=2
				)m2 on a.id=m2.alumno_id
			left join users u on u.id=a.user_id where a.deleted_at is not null';

		return DB::select(DB::raw($consulta));
	}

}