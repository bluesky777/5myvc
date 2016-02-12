<?php namespace App\Http\Controllers;

use Request;
use DB;


use App\Models\User;
use App\Models\VtAspiracion;


class VtParticipantesController extends Controller {

	public function getIndex()
	{

		$participantes = VtParticipante::participantesDeEvento();
		return $participantes;

	}

	public function postIndex()
	{

		$votacion = VtVotacion::where('actual', '=', true)->first();

		try {
			$participante = VtParticipante::create([
				'user_id'		=>	Request::input('user')['id'],
				'votacion_id'	=>	$votacion->id,
				'locked'		=>	Request::input('locked', false),
				'intentos'		=>	0,

			]);
			return $participante;
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}

	public function postInscribirgrupo($grupo_id)
	{
		Eloquent::unguard();

		$votacion = VtVotacion::where('actual', '=', true)->first();

		$consulta = 'SELECT a.id as alumno_id, a.nombres, m.grupo_id, a.user_id FROM alumnos a INNER JOIN matriculas m 
			ON m.alumno_id=a.id AND m.matriculado = 1 AND m.grupo_id = :grupo_id';
		
		$alumnos = DB::select(DB::raw($consulta), array('grupo_id' => $grupo_id));

		$participantes = array();

		for ($i=0; $i < count($alumnos); $i++) { 

			$partic = VtParticipante::where('user_id', '=', $alumnos[$i]->user_id)
					->where('votacion_id', '=', $votacion->id)->get();
			
			if ( count($partic) == 0) {
				try {
					if (!$alumnos[$i]->user_id){
						$dirtyName = $alumnos[$i]->nombres;
						$name = preg_replace('/\s+/', '', $dirtyName);

						$usuario = User::create([
							'username'		=>	$name,
							'password'		=>	'123456',
							'is_superuser'	=>	Request::input('is_superuser', false),
							'is_active'		=>	Request::input('is_active', true),
						]);

						$alumno = Alumno::find($alumnos[$i]->alumno_id);
						$alumno->user_id = $usuario->id;
						$alumno->save();
						$alumnos[$i]->user_id = $alumno->user_id ;
					}
					

					$participante = VtParticipante::create([
						'user_id'		=>	$alumnos[$i]->user_id,
						'votacion_id'	=>	$votacion->id,
						'locked'		=>	false,
						'intentos'		=>	0,
					]);

					array_push($participantes, $participante);

				} catch (Exception $e) {
					//return App::abort('400', 'Datos incorrectos');
					return $e;
				}
			}


		}

		return $participantes;
		
	}

	/**
	 * Display the specified resource.
	 * GET /participantes/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		//
	}

	public function getAllinscritos()
	{
		$votacion = VtVotacion::where('actual', '=', true)->first();

		$consulta = 'SELECT usus.persona_id, vp.id as participante_id, usus.nombres, usus.apellidos, usus.user_id, usus.username, usus.tipo from 
						(select p.id as persona_id, p.nombres, p.apellidos, p.user_id, u.username, ("Pr") as tipo from profesores p inner join users u on p.user_id=u.id
						union
						select a.id as persona_id, a.nombres, a.apellidos, a.user_id, u.username, ("Al") as tipo from alumnos a 
							inner join users u on a.user_id=u.id
							inner join matriculas m on m.alumno_id=a.id and m.matriculado=true
						)usus
					inner join vt_participantes vp on vp.user_id=usus.user_id and vp.votacion_id = :votacion_id';
		
		$participantes = DB::select(DB::raw($consulta), array('votacion_id' => $votacion->id));

		return $participantes;
	}


	/**
	 * Update the specified resource in storage.
	 * PUT /participantes/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function putUpdate($id)
	{
		Eloquent::unguard();
		$participante = VtParticipante::findOrFail($id);
		try {
			$participante->fill([
				'user_id'		=>	Request::input('user_id'),
				'votacion_id'	=>	Request::input('votacion_id'),
				'locked'		=>	Request::input('locked'),
				'intentos'		=>	Request::input('intentos'),

			]);

			$participante->save();
			return $participante;
		} catch (Exception $e) {
			return App::abort('400', 'Datos incorrectos');
			return $e;
		}
	}


	public function deleteDestroy($id)
	{
		$participante = VtParticipante::findOrFail($id);
		$participante->delete();

		return $participante;
	}

}