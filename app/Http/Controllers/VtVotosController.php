<?php namespace App\Http\Controllers;

use Request;
use DB;


use App\Models\User;
use App\Models\VtAspiracion;
use App\Models\VtVoto;
use App\Models\VtCandidato;
use App\Models\VtParticipante;
use App\Models\VtVotacion;
use App\Models\Year;


class VtVotosController extends Controller {


	public function getIndex()
	{
		return VtVoto::all();
	}


	public function postStore()
	{
		$user = User::fromToken();

		$votacionActual = VtVotacion::where('actual', '=', true)->first();
		$aspiracion_id = VtCandidato::find(Request::input('candidato_id'))->aspiracion_id;
		$particip = VtParticipante::participanteDeAspiracion($aspiracion_id, $user);

		if (!$particip) {
			return ['msg'=>'No puede votar ya que no está inscrito como participante'];
		}
		if ($particip->locked == true) {
			return ['msg'=>'Está actualmente bloqueado. Tal vez ya votaste'];
		}


		$particip_id = $particip->id;
		VtVoto::verificarNoVoto($aspiracion_id, $particip_id);

		try {
			$voto = new VtVoto;
			$voto->participante_id	=	$particip_id;
			$voto->candidato_id		=	Request::input('candidato_id');
			$voto->locked			=	false;


			$completos = $this->verificarVotosCompletos($votacionActual->id, $particip_id);

			$particip->locked = $completos;
			$particip->save();

			$voto->completo = $completos; // Para verificar en el frontend cuando se guarde el voto.

			return $voto;
		} catch (Exception $e) {
			return Response::json(array('msg'=>'Error al intentar guardar el voto'), 400);
		}
	}

	public function getShow()
	{
		$user = User::fromToken();

		$votaciones = VtVotacion::actualesInscrito($user, false); // Traer aunque no esté en acción.
		
		$cantVot = count($votaciones);

		for($j=0; $j<$cantVot; $j++){
			
			$aspiraciones = VtAspiracion::where('votacion_id', $votaciones[$j]->votacion_id)->get();
			
			$result = array();

			foreach ($aspiraciones as $aspira) {
				$candidatos = VtCandidato::porAspiracion($aspira->id, $user->year_id);

				for ($i=0; $i<count($candidatos); $i++) {

					$votos = VtVoto::deCandidato($candidatos[$i]->candidato_id, $aspira->id)[0];
					$candidatos[$i]->cantidad = $votos->cantidad;
					$candidatos[$i]->total = $votos->total;
				}

				$aspira->candidatos = $candidatos;
				
				array_push($result, $aspira);
			}

			$votaciones[$j]->aspiraciones = $result;
		}
		return $votaciones;
		
	}


	public function verificarVotosCompletos($votacion_id, $particip_id)
	{
		$aspiraciones = VtAspiracion::where('votacion_id', '=', $votacion_id)->get();
		$cons = 'SELECT vv.participante_id, vv.candidato_id, vp.votacion_id, vv.created_at
				FROM vt_votos vv
				inner join vt_participantes vp on vp.id=vv.participante_id and vv.participante_id=:participante_id
				inner join vt_candidatos vc on vc.id=vv.candidato_id
				inner join vt_aspiraciones va on va.id=vc.aspiracion_id and va.votacion_id=:votacion_id';

		$votosVotados = DB::select(DB::raw($cons), array('votacion_id' => $votacion_id, 'participante_id' => $particip_id));

		$cantVotados = count($votosVotados);

		if ($cantVotados < count($aspiraciones)) {
			$completo = false;
		}else{
			$completo = true;
		}
		return $completo;
	}


	public function putUpdate($id)
	{
		$candidato = VtCandidato::findOrFail($id);
		try {
			$candidato->fill([
				'tipo'		=>	Request::input('tipo'),
				'abrev'		=>	Request::input('abrev')
			]);

			$candidato->save();
		} catch (Exception $e) {
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