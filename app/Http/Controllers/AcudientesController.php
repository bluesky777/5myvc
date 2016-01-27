<?php namespace App\Http\Controllers;



use Request;
use DB;

use App\Models\User;
use App\Models\Acudiente;


class AcudientesController extends Controller {

	/**
	 * Display a listing of the resource.
	 * GET /acudientes
	 *
	 * @return Response
	 */
	public function index()
	{
		return Acudiente::all();
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /acudientes/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /acudientes
	 *
	 * @return Response
	 */
	public function store()
	{
		Eloquent::unguard();
		try {
			$acudiente = Acudiente::create([
				'nombres'	=>	Input::get('nombres'),
				'apellidos'	=>	Input::get('apellidos'),
				'sexo'		=>	Input::get('sexo'),
				'user_id'	=>	Input::get('user_id'),
				'tipo_doc'	=>	Input::get('tipo_doc'),
				'documento'	=>	Input::get('documento'),
				'ciudad_doc'	=>	Input::get('ciudad_doc'),
				'telefono'	=>	Input::get('telefono'),
				'celular'	=>	Input::get('celular'),
				'ciudad_doc'	=>	Input::get('ocupacion'),
				'email'		=>	Input::get('email')

			]);
			return $acudiente;
		} catch (Exception $e) {
			return $e;
		}
	}

	/**
	 * Display the specified resource.
	 * GET /acudientes/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /acudientes/{id}/edit
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
	 * PUT /acudientes/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$acudiente = Acudiente::findOrFail($id);
		try {
			$acudiente->fill([
				'nombres'	=>	Input::get('nombres'),
				'apellidos'	=>	Input::get('apellidos'),
				'sexo'		=>	Input::get('sexo'),
				'user_id'	=>	Input::get('user_id'),
				'tipo_doc'	=>	Input::get('tipo_doc'),
				'documento'	=>	Input::get('documento'),
				'ciudad_doc'	=>	Input::get('ciudad_doc'),
				'telefono'	=>	Input::get('telefono'),
				'celular'	=>	Input::get('celular'),
				'ciudad_doc'	=>	Input::get('ocupacion'),
				'email'		=>	Input::get('email')

			]);

			$acudiente->save();
		} catch (Exception $e) {
			return $e;
		}
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /acudientes/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$acudiente = Acudiente::findOrFail($id);
		$acudiente->delete();

		return $acudiente;
	}

}