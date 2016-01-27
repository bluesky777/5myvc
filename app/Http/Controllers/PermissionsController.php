<?php namespace App\Http\Controllers;

class PermissionsController extends Controller {


	public function getIndex()
	{
		return Permission::all();
	}

	public function postIndex()
	{
		//
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