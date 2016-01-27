<?php namespace App\Http\Controllers;


use Request;
use DB;

use App\Models\User;
use App\Models\EscalaDeValoracion;


class EscalasDeValoracionController extends Controller {

	public function getIndex()
	{
		$user = User::fromToken();

		$escalas = EscalaDeValoracion::where('year_id', '=', $user->year_id)->orderBy('orden')->get();

		return $escalas;
	}


	public function postStore()
	{
		//
	}

	public function getShow($id)
	{
		//
	}


	public function edit($id)
	{
		//
	}


	public function putUpdate($id)
	{
		//
	}


	public function deleteDestroy($id)
	{
		//
	}

}