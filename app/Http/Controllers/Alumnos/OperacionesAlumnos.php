<?php namespace App\Http\Controllers\Alumnos;

use Request;
use DB;
use Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Year;
use App\Models\Periodo;



class OperacionesAlumnos {


	public function dividir_nombre($name)
	{
		$parts          = explode(' ', $name);
		$name_first     = array_shift($parts);
		$name_last      = trim(implode(' ', $parts));
		return ['first' => $name_first, 'last' => $name_last ];

	}



	public function recorrer_y_dividir_nombres(&$alumnos)
	{
		$cant = count($alumnos);

		for ($i=0; $i < $cant; $i++) { 
			$alumnos[$i]->nombres_divididos     = $this->dividir_nombre($alumnos[$i]->nombres);
			$alumnos[$i]->apellidos_divididos   = $this->dividir_nombre($alumnos[$i]->apellidos);

			if($alumnos[$i]->has_sisben){
				$alumnos[$i]->sisben = $alumnos[$i]->nro_sisben;
			}else{
				$alumnos[$i]->sisben = 'No aplica';
			}
		}
		return $alumnos;
	}



}