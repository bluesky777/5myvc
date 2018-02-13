<?php namespace App\Http\Controllers;


use DB;
use Request;

use App\Models\User;
use App\Models\Year;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Unidad;
use App\Models\Grupo;
use App\Http\Controllers\Alumnos\Definitivas;

use App\Http\Controllers\Alumnos\Solicitudes;


class DefinitivasPeriodosController extends Controller {

	public function getIndex()
	{
		$user 			= User::fromToken();

		if ($user->roles[0]->name == 'Profesor') {
			$profe_id = $user->persona_id;
		} else if($user->roles[0]->name == 'User' && $user->is_superuser){
			$profe_id = Request::input('profesor_id');
		}
		
		
		$definitivas 	= new Definitivas();
		$asignaturas 	= $definitivas->asignaturas_docente($profe_id, $user->year_id);
		
		$cantAsig 		= count($asignaturas);
		
		for ($i=0; $i < $cantAsig; $i++) { 
			
			$alumnos = Grupo::alumnos($asignaturas[$i]->grupo_id);
			$asignaturas[$i]->alumnos = $alumnos;
		}
		
		return $asignaturas;
	}


}

