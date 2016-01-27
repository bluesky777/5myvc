<?php namespace App\Http\Controllers;

class VtVotosController extends Controller {

	/**
	 * Display a listing of the resource.
	 * GET /vtvotos
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return VtVoto::all();
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /vtvotos/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /vtvotos
	 *
	 * @return Response
	 */
	public function postStore()
	{
		Eloquent::unguard();

		$user = User::fromToken();

		$votacionActual = VtVotacion::where('actual', '=', true)->first();
		$aspiracion_id = VtCandidato::find(Input::get('candidato_id'))->aspiracion_id;
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
				'candidato_id'		=>	Input::get('candidato_id'),
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


	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /vtvotos/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function putUpdate($id)
	{
		$candidato = VtCandidato::findOrFail($id);
		try {
			$candidato->fill([
				'tipo'		=>	Input::get('tipo'),
				'abrev'		=>	Input::get('abrev')
			]);

			$candidato->save();
		} catch (Exception $e) {
			return $e;
		}
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /vtvotos/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function deleteDestroy($id)
	{
		$candidato = VtCandidato::findOrFail($id);
		$candidato->delete();

		return $candidato;
	}

}