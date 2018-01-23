<?php namespace App\Http\Controllers;


use DB;
use Request;

use App\Models\User;
use App\Models\Year;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Unidad;

use App\Http\Controllers\Alumnos\Solicitudes;


class DefinitivasPeriodosController extends Controller {

	public function getIndex()
	{
		$user = User::fromToken();

		$consulta = 'SELECT a.id, a.materia_id, a.grupo_id, a.profesor_id, a.creditos, a.orden,
						a.created_by, a.updated_by, a.created_at, a.updated_at
					FROM asignaturas a
					inner join grupos g on g.id=a.grupo_id and g.year_id=?
					where a.deleted_at is null
					order by g.orden, a.orden';

		$asignaturas = DB::select($consulta, array($user->year_id));
		return $asignaturas;
	}


}

