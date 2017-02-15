<?php namespace App\Http\Controllers;

use Request;
use DB;


use App\Models\User;
use App\Models\VtCandidato;
use App\Models\VtVotacion;
use App\Models\VtAspiracion;
use App\Models\VtParticipante;
use App\Models\Year;


class VtCandidatosController extends Controller {


	public function getIndex()
	{
		$user = User::fromToken();
		$actual = VtVotacion::actual($user);
		return VtCandidato::all();
	}


	public function postStore()
	{
		$user = User::fromToken();

		$participante_id 	= Request::input('participante_id');
		$aspiracion_id 		= Request::input('aspiracion_id');
		$plancha 			= Request::input('plancha');
		$numero 			= Request::input('numero');
		$locked 			= Request::input('locked', false);

		$busqueda = VtCandidato::where('participante_id', $participante_id)
								->where('aspiracion_id', $aspiracion_id)->first();

		if ( $busqueda ) {
			//return abort(400, 'Candidato ya inscrito.');
			return response()->json([ 'error'=> 400, 'message'=> 'Candidato ya inscrito' ], 400);
		}else{
			$candidato = new VtCandidato;
			$candidato->participante_id		=	$participante_id;
			$candidato->aspiracion_id		=	$aspiracion_id;
			$candidato->plancha				=	$plancha;
			$candidato->numero				=	$numero;
			$candidato->locked				=	$locked;
			$candidato->save();
		}

		try {
			
			return $candidato;
		} catch (Exception $e) {
			//return abort('400', 'Datos incorrectos');
			return $e;
		}
	}


	public function getConaspiraciones()
	{
		$user = User::fromToken();

		if ($user->tipo == 'Alumno' || $user->tipo == 'Acudiente') {
			$votacion = VtVotacion::actualInscrito($user);
		}else{
			$votacion = VtVotacion::actual($user);
			if (!$votacion) {
				return [['sin_votaciones_propias' => true]];
			}
		}
		
		$aspiraciones = VtAspiracion::where('votacion_id', $votacion->id)->get();
		
		$particip = VtParticipante::one($user->id);


		$result = array();

		foreach ($aspiraciones as $aspira) {
			$candidatos = VtCandidato::porAspiracion($aspira->id, $user->year_id);
			$aspira->candidatos = $candidatos;

			$votado = [];
			if ($particip) {
				try {
					$votado = VtVoto::votesInAspiracion($aspira->id, $particip->id);
				} catch (Exception $e) {
					
				}
				
			}

			$aspira->votado = $votado;
			
			array_push($result, $aspira);
		}
		return $result;
	}



	public function update($id)
	{
		$candidato = VtCandidato::findOrFail($id);
		try {
			$candidato->fill([
				'participante_id'	=>	Request::input('participante_id'),
				'aspiracion_id'		=>	Request::input('aspiracion_id'),
				'locked'			=>	Request::input('locked'),

			]);

			$candidato->save();
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}


	public function deleteDestroy($id)
	{
		$candidato = VtCandidato::findOrFail($id);
		$candidato->delete();

		return $candidato;
	}

}