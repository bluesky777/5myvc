<?php namespace App\Http\Controllers;

use Request;
use DB;


use App\Models\User;
use App\Models\VtAspiracion;
use App\Models\VtVotacion;
use App\Models\VtParticipante;
use App\Models\Profesor;


class VtParticipantesController extends Controller {

	public function getIndex()
	{
		$user = User::fromToken();
		$actual = VtVotacion::actual($user);
		
		if($actual) {
			$participantes = VtParticipante::participantesDeEvento($actual->id);
		} else{
			abort(400, 'Debe haber un evento establecido como actual.');
		}
		
		return $participantes;

	}


	public function postInscribirgrupo($grupo_id)
	{
		$user = User::fromToken();

		$votacion = VtVotacion::actual($user);

		$consulta = 'SELECT a.id as alumno_id, a.nombres, m.grupo_id, a.user_id FROM alumnos a INNER JOIN matriculas m 
			ON m.alumno_id=a.id AND m.matriculado = 1 AND m.grupo_id = :grupo_id';
		
		$alumnos = DB::select($consulta, ['grupo_id' => $grupo_id]);

		$participantes = array();

		for ($i=0; $i < count($alumnos); $i++) { 

			$partic = VtParticipante::where('user_id', $alumnos[$i]->user_id)
							->where('votacion_id', $votacion->id)
							->first();
			
			if ( !$partic ) {

				if (!$alumnos[$i]->user_id){
					$dirtyName = $alumnos[$i]->nombres;
					$name = preg_replace('/\s+/', '', $dirtyName);

					$usuario = new User;
					$usuario->username		=	$name;
					$usuario->password		=	'123456';
					$usuario->is_superuser	=	false;
					$usuario->is_active		=	true;
					$usuario->save();

					$alumno = Alumno::find($alumnos[$i]->alumno_id);
					$alumno->user_id = $usuario->id;
					$alumno->save();
					$alumnos[$i]->user_id = $alumno->user_id ;
				}

				$partic_trash = VtParticipante::onlyTrashed()
								->where('user_id', $alumnos[$i]->user_id)
								->where('votacion_id', $votacion->id)
								->first();
			
				
				if ($partic_trash) {
					$participante = $partic_trash;
					$participante->restore();
				}else{

					$participante = new VtParticipante;
					$participante->user_id		=	$alumnos[$i]->user_id;
					$participante->votacion_id	=	$votacion->id;
					$participante->locked		=	false;
					$participante->intentos		=	0;
					$participante->save();
				}


				array_push($participantes, $participante);

			}
		}

		return $participantes;
		
	}


	public function postInscribirProfesores()
	{
		$user = User::fromToken();

		$votacion = VtVotacion::actual($user);

		$profesores = Profesor::fromyear($user->year_id);

		$participantes = [];

		for($i=0; $i < count($profesores); $i++){

			$partic = VtParticipante::where('user_id', $profesores[$i]->user_id)
							->where('votacion_id', $votacion->id)
							->first();
			
			if ( !$partic ) {

				if (!$profesores[$i]->user_id){

					$dirtyName = $profesores[$i]->nombres;
					$name = preg_replace('/\s+/', '', $dirtyName);

					$usuario = new User;
					$usuario->username		=	$name;
					$usuario->password		=	'123456';
					$usuario->is_superuser	=	false;
					$usuario->is_active		=	true;
					$usuario->save();

					$profe = Profesor::find($profesores[$i]->id);
					$profe->user_id = $usuario->id;
					$profe->save();
					$profesores[$i]->user_id = $profe->user_id ;
				}

				$partic_trash = VtParticipante::onlyTrashed()
								->where('user_id', $profesores[$i]->user_id)
								->where('votacion_id', $votacion->id)
								->first();
			
				
				if ($partic_trash) {
					$participante = $partic_trash;
					$participante->restore();
				}else{

					$participante = new VtParticipante;
					$participante->user_id		=	$profesores[$i]->user_id;
					$participante->votacion_id	=	$votacion->id;
					$participante->locked		=	false;
					$participante->intentos		=	0;
					$participante->save();
				}

				array_push($participantes, $participante);
			}

		}

		return $participantes;
	}

	
	public function putSetLocked()
	{
		$user = User::fromToken();
		$id = Request::input('id');
		$locked = Request::input('locked', true);

		$vot = VtParticipante::where('id', $id)->update(['locked' => $locked]);
		return 'Cambiado';
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





	public function deleteDestroy($id)
	{
		$participante = VtParticipante::findOrFail($id);
		$participante->delete();

		return $participante;
	}

}