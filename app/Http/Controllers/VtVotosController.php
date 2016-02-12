<?php namespace App\Http\Controllers;

use Request;
use DB;


use App\Models\User;
use App\Models\VtAspiracion;


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
			return Response::json(array('msg'=>'No puede votar ya que no está inscrito como participante', 400));
		}
		if ($particip->locked == true) {
			return Response::json(array('msg'=>'Está actualmente bloqueado. Tal vez ya votaste'), 400);
		}


		$particip_id = $particip->id;
		VtVoto::verificarNoVoto($aspiracion_id, $particip_id);

		try {
			$voto = VtVoto::create([
				'participante_id'	=>	$particip_id,
				'candidato_id'		=>	Request::input('candidato_id'),
				'locked'			=>	false
			]);

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
		$year = Year::actual();
		$votacion = VtVotacion::actual();
		$aspiraciones = VtAspiracion::where('votacion_id', '=', $votacion->id)->get();
		
		$token = JWTAuth::parseToken();

		if ($token){
			$user = $token->toUser();
		}else{
			$user = (object)array('id'=>2);
			//return Response::json(['error' => 'Token expirado'], 401);
		}

		$result = array();

		foreach ($aspiraciones as $aspira) {
			$candidatos = VtCandidato::porAspiracion($aspira->id, $year->id);

			foreach ($candidatos as $key => $candidato) {

				$votos = VtVoto::deCandidato($candidato->candidato_id, $aspira->id)[0];
				$candidatos[$key]->cantidad = $votos->cantidad;
				$candidatos[$key]->total = $votos->total;
			}

			$aspira->candidatos = $candidatos;
			
			array_push($result, $aspira);
		}
		return $result;
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