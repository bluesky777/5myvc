<?php namespace App\Http\Controllers;

use Request;
use DB;


use App\Models\User;
use App\Models\Unidad;
use App\Models\Subunidad;

use Carbon\Carbon;


class UnidadesController extends Controller {


	private $cons_unidades 		= 'SELECT * FROM unidades WHERE asignatura_id=? and periodo_id=? and deleted_at is null';
	private $cons_subunidades 	= 'SELECT * FROM subunidades WHERE unidad_id=? and deleted_at is null';


	public function getDeAsignaturaPeriodo($asignatura_id, $periodo_id)
	{
		$user = User::fromToken();


		$unidades = DB::select($this->cons_unidades, [$asignatura_id, $periodo_id]);

		//$unidades = Unidad::where('asignatura_id', $asignatura_id)->where('periodo_id', $periodo_id)->get();

		if (count($unidades) == 0) {
			$consulta = 'SELECT * FROM unidades_por_defecto WHERE year_id=? and deleted_at is null';
			$unidades_default = DB::select($consulta, [$user->year_id]);

			if (count($unidades_default) > 0) {
				$now 		= Carbon::now('America/Bogota');

				foreach ($unidades_default as $unidad_d) {

					// Creo las nuevas unidades basado en las unidades por defecto del año
					$consulta 		= 'INSERT INTO unidades(definicion, porcentaje, periodo_id, asignatura_id, obligatoria, orden, por_defecto, created_by, created_at) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?) ';
					$insertadas 	= DB::insert($consulta, [$unidad_d->definicion, $unidad_d->porcentaje, $periodo_id, $asignatura_id, $unidad_d->obligatoria, $unidad_d->orden, true, $user->user_id, $now ]);
					$last_id 	    = DB::getPdo()->lastInsertId();
					
					$consulta 				= 'SELECT * FROM subunidades_por_defecto WHERE unidad_defec_id=? and deleted_at is null';
					$subunidades_default 	= DB::select($consulta, [$unidad_d->id]);
						
					for ($j=0; $j < count($subunidades_default); $j++) { 
						// Creo las subunidades por defecto de cada Unidad por defecto
						$consulta 		= 'INSERT INTO subunidades(definicion, porcentaje, unidad_id, nota_default, obligatoria, orden, por_defecto, created_by, created_at) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?) ';
						$insertadas 	= DB::insert($consulta, [$subunidades_default[$j]->definicion, $subunidades_default[$j]->porcentaje, $last_id, $subunidades_default[$j]->nota_default, $subunidades_default[$j]->obligatoria, $subunidades_default[$j]->orden, true, $user->user_id, $now ]);
		
					}
				}
			}else{
				return '';
			}
			
		}

		// Vuelvo a traer las unidades, por si las moscas
		$unidades = DB::select($this->cons_unidades, [$asignatura_id, $periodo_id]);

		foreach ($unidades as $unidad) {

			$subunidades 			= DB::select($this->cons_subunidades, [$unidad->id]);
			$unidad->subunidades 	= $subunidades;

		}

		return $unidades;
	}



	public function postIndex()
	{
		$user = User::fromToken();

		$cant = Unidad::where('periodo_id', $user->periodo_id)
				->where('asignatura_id', Request::input('asignatura_id'))
				->count();

		$unidad = new Unidad;
		$unidad->definicion		= Request::input('definicion');
		$unidad->porcentaje		= Request::input('porcentaje');
		$unidad->periodo_id		= $user->periodo_id;
		$unidad->created_by		= $user->user_id;
		$unidad->asignatura_id	= Request::input('asignatura_id');
		$unidad->orden			= $cant;
		$unidad->save();

		return $unidad;
	}

	public function putUpdateOrden()
	{
		$user = User::fromToken();

		$sortHash = Request::input('sortHash');

		for($row = 0; $row < count($sortHash); $row++){
			foreach($sortHash[$row] as $key => $value){

				$unidad 			= Unidad::find((int)$key);
				$unidad->orden 		= (int)$value;
				$unidad->save();
			}
		}

		return 'Ordenado correctamente';
	}


	public function putUpdate($id)
	{
		$user = User::fromToken();
		User::pueden_editar_notas($user);

		$unidad = Unidad::findOrFail($id);
		$unidad->definicion		= Request::input('definicion');
		$unidad->porcentaje		= Request::input('porcentaje');
		$unidad->updated_by		= $user->user_id;
		$unidad->save();

		return $unidad;
	}


	public function deleteDestroy($id)
	{
		$user = User::fromToken();
		User::pueden_editar_notas($user);
		$unidad = Unidad::find($id);

		if ($unidad) {
			$unidad->deleted_by = $user->user_id;
			$unidad->save();
			$unidad->delete();
		}else{
			return App::abort(400, 'Unidad no existe o está en Papelera.');
		}
		return $unidad;
	
	}	

	public function deleteForcedelete($id)
	{
		$user = User::fromToken();
		User::pueden_editar_notas($user);

		$unidad = Unidad::onlyTrashed()->findOrFail($id);
		
		if ($unidad) {
			$unidad->forceDelete();
		}else{
			return App::abort(400, 'Unidad no encontrada en la Papelera.');
		}
		return $unidad;
	
	}

	public function putRestore($id)
	{
		$user = User::fromToken();
		$unidad = Unidad::onlyTrashed()->findOrFail($id);

		if ($unidad) {
			$unidad->restore();
		}else{
			return App::abort(400, 'Unidad no encontrada en la Papelera.');
		}
		return $unidad;
	}


	public function getTrashed()
	{
		$user = User::fromToken();
		$consulta = 'SELECT u.id, u.definicion, u.porcentaje, u.periodo_id, u.orden,
						p.numero as numero_periodo, p.actual as periodo_actual, a.id as asignatura_id, a.materia_id,
						m.materia, m.alias as alias_materia, 
						gru.id as grupo_id, gru.nombre as nombre_grupo, gru.abrev as abrev_grupo
					FROM unidades u 
					inner join asignaturas a on a.id=u.asignatura_id
					inner join materias m on m.id=a.materia_id
					inner join grupos gru on gru.id=a.grupo_id
					inner join periodos p on p.id=u.periodo_id
					where u.deleted_at is not null';

		return DB::select(DB::raw($consulta));
	}

}