<?php namespace App\Http\Controllers;


use Request;
use DB;

use App\Models\User;
use App\Models\ChangeAsked;
use App\Models\Alumno;


class ChangeAskedController extends Controller {


	public function getToMe()
	{
		$user = User::fromToken();

		// Consulta con DB compleja que traiga las peticiones de los alumnos de su titularía.
		$cambios = ChangeAsked::all();
		return $cambios;
	}


	public function store()
	{
		//
	}


	public function update($id)
	{
		//
	}

	public function destroy($id)
	{
		//
	}

}