<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\NotaComportamiento;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\Frase;


class NotaComportamientoController extends Controller {

	public function getIndex()
	{
		$user = User::fromToken();
		return NotaComportamiento::all();
	}

	public function getDetailed($grupo_id)
	{
		$user = User::fromToken();
		$nota_max = DB::select('SELECT id, desempenio, porc_inicial, porc_final FROM escalas_de_valoracion 
					where deleted_at is null and year_id=? order by orden desc limit 1', [$user->year_id])[0];
		$nota_max = $nota_max->porc_final;
		$alumnos = Grupo::alumnos($grupo_id);

		foreach ($alumnos as $alumno) {

			$userData = Alumno::userData($alumno->alumno_id);
			$alumno->userData = $userData;

			$nota = NotaComportamiento::crearVerifNota($alumno->alumno_id, $user->periodo_id, $nota_max);

			$consulta = 'SELECT * FROM (
							SELECT d.id as definicion_id, d.comportamiento_id, d.frase_id, 
								f.frase, f.tipo_frase, f.year_id
							FROM definiciones_comportamiento d
							inner join frases f on d.frase_id=f.id and d.deleted_at is null 
						    where d.comportamiento_id=:comportamiento1_id and f.deleted_at is null
						union
							select d2.id as definicion_id, d2.comportamiento_id, d2.frase_id, 
								d2.frase, null as tipo_frase, null as year_id
							from definiciones_comportamiento d2 where d2.deleted_at is null and d2.frase is not null                  
							  and d2.comportamiento_id=:comportamiento2_id 
							
						) defi';

			$definiciones = DB::select($consulta, array('comportamiento1_id' => $nota->id, 'comportamiento2_id' => $nota->id));
			
			$alumno->definiciones = $definiciones;
			$alumno->nota = $nota;
		}

		$frases = Frase::where('year_id', '=', $user->year_id)->get();
		$grupo = Grupo::find($grupo_id);

		$resultado = [];

		array_push($resultado, $frases);
		array_push($resultado, $alumnos);
		array_push($resultado, $grupo);

		return $resultado;
	}

	public function postStore()
	{
		$user = User::fromToken();

		$nota = new NotaComportamiento;

		$nota->alumno_id	=	Request::input('alumno_id');
		$nota->periodo_id	=	$user->periodo_id;
		$nota->nota			=	Request::input('nota');

		$nota->save();
		return $nota;
	}


	public function getShow($id)
	{
		//
	}


	public function putUpdate($id)
	{
		$user = User::fromToken();

		$nota = NotaComportamiento::findOrFail($id);

		$nota->nota = Request::input('nota');

		$nota->save();
		$nota = NotaComportamiento::findOrFail($id);
		return $nota;
	}


	public function deleteDestroy($id)
	{
		$nota = NotaComportamiento::findOrFail($id);
		$nota->delete();

		return $nota;
	}

}