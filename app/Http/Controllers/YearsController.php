<?php namespace App\Http\Controllers;

use DB;
use Request;

use App\Models\User;
use App\Models\Year;
use App\Models\Periodo;
use App\Models\ConfigCertificado;
use App\Models\ImageModel;
use App\Models\Grupo;
use App\Models\Asignatura;
use App\Models\EscalaDeValoracion;
use App\Models\Frase;


class YearsController extends Controller {


	public function getIndex()
	{
		$user = User::fromToken();

		$years = Year::all();

		foreach ($years as $year) {
			$year->periodos = Periodo::where('year_id', '=', $year->id)->get();
		}


		return $years;
	}


	public function getColegio()
	{
		$user = User::fromToken();

		$years 		= Year::all();

		foreach ($years as $year) {
			$year->periodos = Periodo::where('year_id', $year->id)->get();
		}

		$certif 	= ConfigCertificado::all();

		$imagenes 	= ImageModel::where('user_id', $user->user_id)
							->where('publica', true)
							->get();

		$result = ['years' => $years, 'certificados' => $certif, 'imagenes' => $imagenes];

		return $result;
	}

	

	public function postStore()
	{
		$user = User::fromToken();

		$year = new Year;

		$year->year						=	Request::input('year');
		$year->nombre_colegio			=	Request::input('nombre_colegio');
		$year->abrev_colegio			=	Request::input('abrev_colegio');
		$year->nota_minima_aceptada		=	Request::input('nota_minima_aceptada');
		$year->resolucion				=	Request::input('resolucion');
		$year->codigo_dane				=	Request::input('codigo_dane');
		//$year->encabezado_certificado	=	Request::input('encabezado_certificado');
		//$year->frase_final_certificado	=	Request::input('frase_final_certificado');
		$year->actual					=	Request::input('actual');
		$year->telefono					=	Request::input('telefono');
		$year->celular					=	Request::input('celular');
		
		$year->unidad_displayname		=	Request::input('unidad_displayname');
		$year->unidades_displayname		=	Request::input('unidades_displayname');
		$year->genero_unidad			=	Request::input('genero_unidad');
		$year->subunidad_displayname	=	Request::input('subunidad_displayname');
		$year->subunidades_displayname	=	Request::input('subunidades_displayname');
		$year->genero_subunidad			=	Request::input('genero_subunidad');
		
		$year->website					=	Request::input('website');
		$year->website_myvc				=	Request::input('website_myvc');
		$year->alumnos_can_see_notas	=	Request::input('alumnos_can_see_notas');

		$year->save();
		

		if ($year->actual) {
			Year::where('actual', true)->update(['actual'=>false]);
		}



		// NECESITARÉ MUCHO EL AÑO ANTERIOR
		$year_ante = $year->year - 1;
		$pasado = Year::where('year', $year_ante)->first();



		if ($pasado) {


			$year->ciudad_id				=	$pasado->ciudad_id;
			$year->logo_id					=	$pasado->logo_id;
			$year->rector_id				=	$pasado->rector_id;
			$year->secretario_id			=	$pasado->secretario_id;
			$year->tesorero_id				=	$pasado->tesorero_id;
			$year->coordinador_academico_id	=	$pasado->coordinador_academico_id;
			$year->coordinador_disciplinario_id	=	$pasado->coordinador_disciplinario_id;
			$year->capellan_id				=	$pasado->capellan_id;
			$year->psicorientador_id 		=	$pasado->psicorientador_id;

			$year->save();
			
			/// COPIAREMOS LAS ESCALAS DE VALORACIÓN
			$escalas_ant = EscalaDeValoracion::where('year_id', $pasado->id)->get();

			foreach ($escalas_ant as $key => $escalas) {
				$newEsc = new EscalaDeValoracion;
				$newEsc->desempenio 	= $escalas->desempenio;
				$newEsc->valoracion 	= $escalas->valoracion;
				$newEsc->porc_inicial 	= $escalas->porc_inicial;
				$newEsc->porc_final 	= $escalas->porc_final;
				$newEsc->descripcion 	= $escalas->descripcion;
				$newEsc->orden 			= $escalas->orden;
				$newEsc->perdido 		= $escalas->perdido;
				$newEsc->year_id 		= $year->id;
				$newEsc->icono_infantil = $escalas->icono_infantil;
				$newEsc->icono_adolescente = $escalas->icono_adolescente;
				$newEsc->save();
			}


			/// COPIAREMOS LAS FRASES
			$frases_ant = Frase::where('year_id', $pasado->id)->get();

			foreach ($frases_ant as $key => $frases) {
				$newFra = new Frase;
				$newFra->frase 			= $frases->frase;
				$newFra->tipo_frase 	= $frases->tipo_frase;
				$newFra->year_id 		= $year->id;
				$newFra->save();
			}


			
			/// AHORA COPIAMOS LOS GRUPOS Y ASIGNATURAS DEL AÑO PASADO AL NUEVO AÑO.
			$grupos_ant = Grupo::where('year_id', $pasado->id)->get();
			
			foreach ($grupos_ant as $key => $grupo) {
				$newGr = new Grupo;
				$newGr->nombre 			= $grupo->nombre;
				$newGr->abrev 			= $grupo->abrev;
				$newGr->year_id 		= $year->id;
				$newGr->grado_id 		= $grupo->grado_id;
				$newGr->valormatricula 	= $grupo->valormatricula;
				$newGr->valorpension 	= $grupo->valorpension;
				$newGr->orden 			= $grupo->orden;
				$newGr->caritas 		= $grupo->caritas;
				$newGr->save();


				$asigs_ant = Asignatura::where('grupo_id', $grupo->id)->get();
			
				foreach ($asigs_ant as $key => $asig) {
					$newAsig = new Asignatura;
					$newAsig->materia_id 	= $asig->materia_id;
					$newAsig->grupo_id 		= $asig->grupo_id;
					$newAsig->creditos 		= $asig->creditos;
					$newAsig->orden 		= $asig->orden;
					$newAsig->save();
				}
				$grupo->asigs_ant = $asigs_ant;
			}
			$year->grupos_ant = $grupos_ant;
		}
		


		return $year;
		
	}




	public function putUseractive($year_id)
	{
		$user = User::fromToken();

		$usuario = User::findOrFail($user->user_id);


		$peri = Periodo::where('year_id', $year_id)->where('numero', $user->numero_periodo)->first();

		if ($peri) {
			$usuario->periodo_id = $peri->id;
		}else{
			$peris = Periodo::where('year_id', $year_id)->get();

			if (count($peris) > 0) {
				$peri = $peris[count($peris)-1];
				$usuario->periodo_id = $peri->id;
			}else{
				abort(400, 'Año sin ningún periodo.');
			}
			
		}

		

		$usuario->save();

		return $peri;
	}





	public function putUpdate($id)
	{
		$year = Year::findOrFail($id);
		try {
			$year->tipo				=	Request::input('tipo');
			$year->nombre_colegio	=	Request::input('nombre_colegio');
			$year->abrev_colegio	=	Request::input('abrev_colegio');
			$year->rector			=	Request::input('rector');
			$year->sexo_rector		=	Request::input('sexo_rector');
			$year->secretario		=	Request::input('secretario');
			$year->sexo_secretario	=	Request::input('sexo_secretario');
			$year->resolucion		=	Request::input('resolucion');
			$year->codigo_dane		=	Request::input('codigo_dane');
			$year->encabezado_certificado=	Request::input('encabezado_certificado');
			$year->frase_final_certificado=	Request::input('frase_final_certificado');
			$year->actual			=	Request::input('actual');
			$year->telefono			=	Request::input('telefono');
			$year->celular			=	Request::input('celular');
			$year->website			=	Request::input('website');
			$year->website_myvc		=	Request::input('website_myvc');
			$year->alumnos_can_see_notas	=	Request::input('alumnos_can_see_notas');

			$year->save();
		} catch (Exception $e) {
			return $e;
		}
	}

	

	public function putAlumnosCanSeeNotas($can){
		$user = User::fromToken();

		$year = Year::findOrFail($user->year_id);
		$year->alumnos_can_see_notas = $can;

		$year->save();

		if ($can) {
			return 'Ahora pueden ver sus notas.';
		}else{
			return 'Ahora NO pueden ver sus notas';
		}
		
	}


	public function deleteDelete($id)
	{
		$user = User::fromToken();
		
		$year = Year::findOrFail($id);
		$year->delete();

		return $year;
	}



	public function deleteDestroy($id)
	{
		$user = User::fromToken();
		
		$year = Year::onlyTrashed()->findOrFail($id);
		$year->forceDelete();

		return $year;
	}

	public function putRestore($id)
	{
		$year = Year::onlyTrashed()->findOrFail($id);

		if ($year) {
			$year->restore();
		}else{
			return abort(400, 'Año no encontrado en la Papelera.');
		}
		return $year;
	}


	public function getTrashed()
	{
		$years = Year::onlyTrashed()->get();

		return $years;
	}



}


