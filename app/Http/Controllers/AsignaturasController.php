<?php namespace App\Http\Controllers;


use DB;
use Request;

use App\Models\User;
use App\Models\Year;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Unidad;


class AsignaturasController extends Controller {

	public function getIndex()
	{
		$user = User::fromToken();

		$consulta = 'SELECT a.id, a.materia_id, a.grupo_id, a.profesor_id, a.creditos, a.orden,
						a.created_by, a.updated_by, a.created_at, a.updated_at
					FROM asignaturas a
					inner join grupos g on g.id=a.grupo_id and g.year_id=?
					where a.deleted_at is null
					order by g.orden, a.orden';

		$asignaturas = DB::select(DB::raw($consulta), array($user->year_id));
		return $asignaturas;
	}

	public function postIndex()
	{
		
		$this->fixInputs();

		$asignatura = new Asignatura;
		$asignatura->materia_id		=	Request::input('materia_id');
		$asignatura->grupo_id		=	Request::input('grupo_id');
		$asignatura->profesor_id	=	Request::input('profesor_id');
		$asignatura->creditos		=	Request::input('creditos');
		$asignatura->orden			=	Request::input('orden');
		$asignatura->save();

		return $asignatura;
	}

	public function getShow($asignatura_id)
	{
		$user = User::fromToken();
		$asignatura = Asignatura::detallada($asignatura_id, $user->year_id);
		return $asignatura;
	}

	public function putUpdate($id)
	{
		$asignatura = Asignatura::findOrFail($id);

		$this->fixInputs();

		$asignatura->materia_id		=	Request::input('materia_id');
		$asignatura->grupo_id		=	Request::input('grupo_id');
		$asignatura->profesor_id	=	Request::input('profesor_id');
		$asignatura->creditos		=	Request::input('creditos');
		$asignatura->orden			=	Request::input('orden');

		$asignatura->save();
		return $asignatura;
	}

	private function fixInputs()
	{
		if (!Request::input('profesor_id') and Request::input('profesor')['id']) {
			Request::merge(array('profesor_id' => Request::input('profesor')['id'] ) );
		}

		if (!Request::input('grupo_id') and Request::input('grupo')['id']) {
			Request::merge(array('grupo_id' => Request::input('grupo')['id'] ) );
		}

		if (!Request::input('materia_id') and Request::input('materia')['id']) {
			Request::merge(array('materia_id' => Request::input('materia')['id'] ) );
		}
	}


	public function getListasignaturas($persona_id='')
	{
		$user = User::fromToken();

		if ($persona_id=='') {
			$persona_id = $user->persona_id;
		}

		$consulta = '';
		$asignaturas = '';

		switch ($user->tipo) {
			case 'Profesor' or 'Usuario':
				$asignaturas = Profesor::asignaturas($user->year_id, $persona_id);

				foreach ($asignaturas as $asignatura) {

					$asignatura->unidades = Unidad::informacionAsignatura($asignatura->asignatura_id, $user->periodo_id);
					
				}


				break;

			case 'Alumno':
				$consulta = 'SELECT a.id as asignatura_id, a.grupo_id, a.profesor_id, a.creditos, a.orden,
							m.materia, m.alias as alias_materia, g.nombre as nombre_grupo, g.abrev as abrev_grupo, g.titular_id, g.caritas
						FROM asignaturas a
						inner join materias m on m.id=a.materia_id and m.deleted_at is null
						inner join grupos g on g.id=a.grupo_id and g.year_id=:year_id and g.deleted_at is null
						where a.profesor_id=:profesor_id and a.deleted_at is null
						order by g.orden, a.orden, a.id';

				$asignaturas = DB::select(DB::raw($consulta), array(':year_id' => $user->year_id, ':profesor_id' => $persona_id));
				
				break;
			
			default:
				# code...
				break;
		}

		

		return $asignaturas;
	}

	public function getListasignaturasyear($profesor_id, $periodo_id)
	{
		$user = User::fromToken();

		$year = Year::de_un_periodo($periodo_id);

		$asignaturas = Profesor::asignaturas($year->id, $profesor_id);

		foreach($asignaturas as $asignatura) {

			$asignatura->unidades = Unidad::informacionAsignatura($asignatura->asignatura_id, $periodo_id);
			
		}
		

		return $asignaturas;
	}


	public function deleteDestroy($id)
	{
		$asignatura = Asignatura::findOrFail($id);
		$asignatura->delete();

		return $asignatura;
	}

}

