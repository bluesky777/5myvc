<?php namespace App\Http\Controllers;

class VtCandidatosController extends Controller {

	/**
	 * Display a listing of the resource.
	 * GET /candidatos
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return VtCandidato::all();
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /candidatos/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /candidatos
	 *
	 * @return Response
	 */
	public function postStore()
	{
		Eloquent::unguard();

		$participante_id = Input::get('participante_id');
		$aspiracion_id = Input::get('aspiracion_id');
		$plancha = Input::get('plancha');
		$numero = Input::get('numero');
		$locked = Input::get('locked', false);

		$busqueda = VtCandidato::where('participante_id', '=', $participante_id)
					->where('aspiracion_id', '=', $aspiracion_id)->get();

		if ( count($busqueda) > 0 ) {
			return App::abort('400', 'Candidato ya inscrito.');
		}else{
			$candidato = VtCandidato::create([
				'participante_id'	=>	$participante_id,
				'aspiracion_id'		=>	$aspiracion_id,
				'plancha'			=>	$plancha,
				'numero'			=>	$numero,
				'locked'			=>	$locked,
			]);
		}

		try {
			
			return $candidato;
		} catch (Exception $e) {
			//return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}

	/**
	 * Display the specified resource.
	 * GET /candidatos/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}
	public function getConaspiraciones()
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

		$particip = VtParticipante::one($user->id);


		$result = array();

		foreach ($aspiraciones as $aspira) {
			$candidatos = VtCandidato::porAspiracion($aspira->id, $year->id);
			$aspira->candidatos = $candidatos;

			if ($particip) {
				$votado = [];
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

	/**
	 * Show the form for editing the specified resource.
	 * GET /candidatos/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /candidatos/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$candidato = VtCandidato::findOrFail($id);
		try {
			$candidato->fill([
				'participante_id'	=>	Input::get('participante_id'),
				'aspiracion_id'		=>	Input::get('aspiracion_id'),
				'locked'			=>	Input::get('locked'),

			]);

			$candidato->save();
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /candidatos/{id}
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