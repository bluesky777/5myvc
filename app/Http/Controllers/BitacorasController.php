<?php namespace App\Http\Controllers;

use App\Models\User;
use DB;

class BitacorasController extends Controller {

	public function getIndex($user_id='')
	{
		$user = User::fromToken();

		if ($user_id=='') {
			$user_id = $user->user_id;
		}

		$consulta = 'SELECT * FROM bitacoras where created_by=? order by id desc ';
		$bits = DB::select($consulta, array($user_id));

		return $bits;
	}

	public function postStore()
	{
		
	}


	public function getShow($id)
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