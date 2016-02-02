<?php namespace App\Http\Controllers;

use DB;
use Request;

use App\Models\User;
use App\Models\Year;
use App\Models\Periodo;
use App\Models\configCertificado;
use App\Models\ImageModel;


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
			$year->periodos = Periodo::where('year_id', '=', $year->id)->get();
		}

		$certif 	= configCertificado::all();

		$imagenes 	= ImageModel::where('user_id', '=', $user->user_id)->get();

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
		//$year->ciudad_id				=	Request::input('ciudad_id');
		//$year->logo_id			=	Request::input('logo_id');
		//$year->img_encabezado_id=	Request::input('img_encabezado_id');
		//$year->rector_id			=	Request::input('rector_id');
		//$year->secretario_id		=	Request::input('secretario_id');
		//$year->tesorero_id		=	Request::input('tesorero_id');
		//$year->coordinador_academico_id		=	Request::input('coordinador_academico_id');
		//$year->coordinador_disciplinario_id	=	Request::input('coordinador_disciplinario_id');
		//$year->capellan_id		=	Request::input('capellan_id');
		//$year->psicorientador_id=	Request::input('psicorientador_id');
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



		if ($year->actual) {
			Year::where('actual', true)->update(['actual'=>false]);
		}



		$year->save();

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

	

	public function deleteDestroy($id)
	{
		$user = User::fromToken();
		
		$year = Year::findOrFail($id);
		$year->delete();

		return $year;
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

}


