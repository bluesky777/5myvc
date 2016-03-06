<?php namespace App\Http\Controllers;


use DB;
use Request;

use App\Models\User;
use App\Models\Year;
use App\Models\Grado;
use App\Models\Profesor;
use App\Models\Grupo;



class GruposController extends Controller {


	public function getIndex()
	{
		$user = User::fromToken();
		$consulta = 'SELECT g.id, g.nombre, g.abrev, g.orden, g.grado_id, g.year_id, g.titular_id,
			p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
			g.created_at, g.updated_at, gra.nombre as nombre_grado 
			from grupos g
			inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
			left join profesores p on p.id=g.titular_id
			where g.deleted_at is null
			order by g.orden';

		$grados = DB::select($consulta, array(':year_id'=>$user->year_id));

		return $grados;
	}



	public function getListado($grupo_id)
	{
		$user = User::fromToken();
		$consulta = 'SELECT m.alumno_id, a.user_id, u.username, a.nombres, a.apellidos, a.sexo, a.fecha_nac,
						u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
						a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre,
						(a.direccion + " - " + a.barrio) as direccion, a.facebook, a.pazysalvo, a.deuda
					FROM alumnos a
					inner join matriculas m on m.alumno_id=a.id and m.grupo_id=:grupo_id and m.deleted_at is null 
					left join users u on u.id=a.user_id
					left join images i on i.id=u.imagen_id
					left join images i2 on i2.id=a.foto_id
					where a.deleted_at is null order by apellidos, nombres';

		$list = DB::select(DB::raw($consulta), array(':grupo_id'=>$grupo_id));

		return $list;
	}


	public function postStore()
	{
		
		$user = User::fromToken();

		try {

			$titular_id = null;
			$grado_id = null;

			if (Request::input('titular_id')) {
				$titular_id = Request::input('titular_id');
			}else if (Request::input('titular')) {
				$titular_id = Request::input('titular')['profesor_id'];
			}else{
				$titular_id = null;
			}

			if (Request::input('grado_id')) {
				$grado_id = Request::input('grado_id');
			}else if (Request::input('grado')) {
				$grado_id = Request::input('grado')['id'];
			}else{
				$grado_id = null;
			}

			$grupo = new Grupo;
			$grupo->nombre		=	Request::input('nombre');
			$grupo->abrev		=	Request::input('abrev');
			$grupo->year_id		=	$user->year_id;
			$grupo->titular_id	=	$titular_id;
			$grupo->grado_id	=	Request::input('grado')['id'];
			$grupo->valormatricula=	Request::input('valormatricula');
			$grupo->valorpension=	Request::input('valorpension');
			$grupo->orden		=	Request::input('orden');
			$grupo->caritas		=	Request::input('caritas');
			$grupo->save();
			
			return $grupo;
		} catch (Exception $e) {
			return abort('400', $e);
			return $e;
		}
	}


	public function getShow($id)
	{
		$grupo = Grupo::findOrFail($id);

		$profesor = Profesor::find($grupo->titular_id);
		$grupo->titular = $profesor;

		$grado = Grado::findOrFail($grupo->grado_id);
		$grupo->grado = $grado;

		return $grupo;
	}


	public function putUpdate()
	{
		$user = User::fromToken();
		$grupo = Grupo::findOrFail(Request::input('id'));

		try {

			$titular_id = null;
			$grado_id = null;

			if (Request::input('titular_id')) {
				$titular_id = Request::input('titular_id');
			}else if (Request::input('titular')) {
				$titular_id = Request::input('titular')['profesor_id'];
			}else{
				$titular_id = null;
			}

			if (Request::input('grado_id')) {
				$grado_id = Request::input('grado_id');
			}else if (Request::input('grado')) {
				$grado_id = Request::input('grado')['id'];
			}else{
				$grado_id = null;
			}

			$grupo->nombre		=	Request::input('nombre');
			$grupo->abrev		=	Request::input('abrev');
			$grupo->year_id		=	$user->year_id;
			$grupo->titular_id	=	$titular_id;
			$grupo->grado_id	=	$grado_id;
			$grupo->valormatricula=	Request::input('valormatricula');
			$grupo->valorpension=	Request::input('valorpension');
			$grupo->orden		=	Request::input('orden');
			$grupo->caritas		=	Request::input('caritas');

			$grupo->save();

			return $grupo;
		} catch (Exception $e) {
			return abort('400', 'Datos incorrectos');
			return $e;
		}
	}



	public function deleteDestroy($id)
	{
		$grupo = Grupo::findOrFail($id);
		$grupo->delete();

		return $grupo;
	}
	public function deleteForcedelete($id)
	{
		$user = User::fromToken();
		$grupo = Grupo::onlyTrashed()->findOrFail($id);
		
		if ($grupo) {
			$grupo->forceDelete();
		}else{
			return abort(400, 'Grupo no encontrado en la Papelera.');
		}
		return $grupo;
	
	}

	public function putRestore($id)
	{
		$user = User::fromToken();
		$grupo = Grupo::onlyTrashed()->findOrFail($id);

		if ($grupo) {
			$grupo->restore();
		}else{
			return abort(400, 'Grupo no encontrado en la Papelera.');
		}
		return $grupo;
	}



	public function getTrashed()
	{
		$grupos = Grupo::onlyTrashed()->get();
		return $grupos;
	}

}