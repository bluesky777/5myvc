<?php namespace App\Http\Controllers;


use DB;
use Request;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Year;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Unidad;
use App\Models\Grupo;
use App\Models\NotaFinal;
use App\Http\Controllers\Alumnos\Definitivas;

use App\Http\Controllers\Alumnos\Solicitudes;


class DefinitivasPeriodosController extends Controller {

	public function getIndex()
	{
		$user 			= User::fromToken();

		if ($user->roles[0]->name == 'Profesor') {
			$profe_id = $user->persona_id;
		} else if($user->roles[0]->name == 'Admin' && $user->is_superuser){
			$profe_id = Request::input('profesor_id');
		}
		
		
		$definitivas 	= new Definitivas();
		$asignaturas 	= $definitivas->asignaturas_docente($profe_id, $user->year_id);
		
		$cantAsig 		= count($asignaturas);
		
		for ($i=0; $i < $cantAsig; $i++) { 
			
			$asignaturas[$i]->alumnos = NotaFinal::alumnos_grupo_nota_final($asignaturas[$i]->grupo_id, $asignaturas[$i]->asignatura_id, $user->user_id);
			
		}
		
		return $asignaturas;
	}


	public function putCalcularNotasFinalesAsignatura()
	{
		$user 			= User::fromToken();

		if ($user->roles[0]->name == 'Profesor' || ($user->roles[0]->name == 'User' && $user->is_superuser)) {
			$asignatura_id 	= Request::input('profesor_id');
		}else{
			return 'No tienes privilegios';
		}
		
		$definitivas 	= new Definitivas();
		$definitivas->calcular_notas_finales_asignatura($asignatura_id);
		
		$cantAsig 		= count($asignaturas);
		
		for ($i=0; $i < $cantAsig; $i++) { 
			
			$asignaturas[$i]->alumnos = NotaFinal::alumnos_grupo_nota_final($asignaturas[$i]->grupo_id, $asignaturas[$i]->asignatura_id);
			
		}
		
		return $asignaturas;
	}


	public function putUpdate()
	{
		$user 			= User::fromToken();

		if ($user->roles[0]->name == 'Profesor' || ($user->roles[0]->name == 'Admin' && $user->is_superuser)) {
			// No pasa nada
		}else{
			return App::abort(400, 'No tienes privilegios.');
		}
		
		$now 		= Carbon::now('America/Bogota');
		$consulta 	= 'UPDATE notas_finales SET nota=?, manual=true, updated_by=?, updated_at=? WHERE id=?';
		
		DB::update($consulta, [ Request::input('nota'), $user->user_id, $now, Request::input('nf_id') ]);
		
		return 'Cambiada';
	}


	public function putToggleRecuperada()
	{
		$user 			= User::fromToken();

		if ($user->roles[0]->name == 'Profesor' || ($user->roles[0]->name == 'Admin' && $user->is_superuser)) {
			// No pasa nada
		}else{
			return App::abort(400, 'No tienes privilegios.');
		}
		$now 		= Carbon::now('America/Bogota');
		$recu 		= Request::input('recuperada');
		
		if ($recu) {
			$consulta 	= 'UPDATE notas_finales SET recuperada=?, manual=?, updated_by=?, updated_at=? WHERE id=?';
			DB::update($consulta, [ $recu, true, $user->user_id, $now, Request::input('nf_id') ]);
		}else{
			$consulta 	= 'UPDATE notas_finales SET recuperada=?, updated_by=?, updated_at=? WHERE id=?';
			DB::update($consulta, [ $recu, $user->user_id, $now, Request::input('nf_id') ]);
		}
		
		return 'Cambiada';
	}



	public function putToggleManual()
	{
		$user 			= User::fromToken();

		if ($user->roles[0]->name == 'Profesor' || ($user->roles[0]->name == 'Admin' && $user->is_superuser)) {
			// No pasa nada
		}else{
			return App::abort(400, 'No tienes privilegios.');
		}
		$now 		= Carbon::now('America/Bogota');
		$manual 	= Request::input('manual');
		if ($manual){
			$consulta 	= 'UPDATE notas_finales SET manual=?, updated_by=?, updated_at=? WHERE id=?';
			DB::update($consulta, [ $manual, $user->user_id, $now, Request::input('nf_id') ]);
		}else{
			$consulta 	= 'UPDATE notas_finales SET manual=?, recuperada=?, updated_by=?, updated_at=? WHERE id=?';
			DB::update($consulta, [ $manual, true, $user->user_id, $now, Request::input('nf_id') ]);
		}
		
		return 'Cambiada';
	}


}

