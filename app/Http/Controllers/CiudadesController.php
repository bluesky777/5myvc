<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Ciudad;
use App\Models\Pais;


class CiudadesController extends Controller {

	public function getIndex()
	{
		return Ciudad::all();
	}

	
	public function getDepartamentos($pais_id)
	{	
		$consulta = 'SELECT distinct departamento FROM ciudades where pais_id = :pais';
		return DB::select(DB::raw($consulta), array('pais' => $pais_id));
	}

	public function getPaisdeciudad($ciudad_id)
	{	
		$consulta = 'SELECT paises.id, pais, abrev FROM paises, ciudades where paises.id = ciudades.pais_id and ciudades.id = :ciudad_id';
		return DB::select(DB::raw($consulta), array('ciudad_id' => $ciudad_id));
	}

	public function getPordepartamento($departamento)
	{
		return Ciudad::where('departamento', $departamento)->get();
	}


	public function getDatosciudad($ciudad_id)
	{
		$ciudad = Ciudad::find($ciudad_id);
		$pais = $this->getPaisdeciudad($ciudad->id);

		$departamentos = $this->getDepartamentos($pais[0]->id);
		$ciudades = Ciudad::where('departamento' , $ciudad->departamento)->get();

		$result = array('ciudad' => $ciudad, 
						'ciudades' => $ciudades, 
						'departamento' => array('departamento'=>$ciudad->departamento), 
						'departamentos' => $departamentos,
						'pais'=> $pais[0],
						'paises' => Pais::all());
		return $result;
	}


	public function store()
	{
		
		try {
			$ciudad = new Ciudad;
			$ciudad->ciudad			=	Request::input('ciudad');
			$ciudad->departamento	=	Request::input('departamento');
			$ciudad->pais_id		=	Request::input('pais_id');
			$ciudad->save();
			
			return $ciudad;
		} catch (Exception $e) {
			return $e;
		}
	}


	public function update($id)
	{
		$ciudad = Ciudad::findOrFail($id);
		try {
			$ciudad->ciudad			=	Request::input('ciudad');
			$ciudad->departamento	=	Request::input('departamento');
			$ciudad->pais_id		=	Request::input('pais_id');
			$ciudad->save();

		} catch (Exception $e) {
			return $e;
		}
	}


	public function destroy($id)
	{
		$ciudad = Ciudad::findOrFail($id);
		$ciudad->delete();

		return $ciudad;
	}

}