<?php namespace App\Http\Controllers\Alumnos;



use Request;
use DB;
use Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Year;
use App\Models\Periodo;



class GuardarAlumno {


	public function valor($user, $alumno, $propiedad, $valor, $user_id)
	{

		$consulta 	= '';
		$datos 		= [];
		$now 		= Carbon::now('America/Bogota');

		if ($propiedad == 'fecha_nac')
			$valor = Carbon::parse($valor);

		switch ($propiedad) {
			case 'username':
				$consulta 	= 'UPDATE users SET username=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:user_id';
				$datos 		= [ ':valor' => $valor, ':modificador' => $user_id, ':fecha' => $now, ':user_id' => $alumno->user_id ];
				break;
			
			default:
				$consulta = 'UPDATE alumnos SET '.$propiedad.'=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:alumno_id';
				$datos 		= [
					':valor'		=> $valor, 
					':modificador'	=> $user_id, 
					':fecha' 		=> $now,
					':alumno_id'	=> $alumno->id
				];
				break;
		}
		
		
		$consulta = DB::raw($consulta);

		$res = DB::update($consulta, $datos);

		if($res)
			return 'Guardado';
		else
			return 'No guardado';

	}



	public function valorAcudiente($acudiente_id, $parentesco_id, $user_acud_id, $propiedad, $valor, $user_id)
	{

		$consulta 	= '';
		$datos 		= [];
		$now 		= Carbon::now('America/Bogota');

		if ($propiedad == 'fecha_nac')
			$valor = Carbon::parse($valor);

		switch ($propiedad) {
			case 'username':
				$consulta 	= 'UPDATE users SET username=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:user_id';
				$datos 		= [ ':valor' => $valor, ':modificador' => $user_id, ':fecha' => $now, ':user_id' => $user_acud_id ];
				break;
			
			case 'parentesco':
				$consulta 	= 'UPDATE parentescos SET parentesco=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:parentesco_id';
				$datos 		= [ ':valor' => $valor, ':modificador' => $user_id, ':fecha' => $now, ':parentesco_id' => $parentesco_id ];
				break;
			
			default:
				$consulta = 'UPDATE acudientes SET '.$propiedad.'=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:acudiente_id';
				$datos 		= [
					':valor'		=> $valor, 
					':modificador'	=> $user_id, 
					':fecha' 		=> $now,
					':acudiente_id'	=> $acudiente_id
				];
				break;
		}
		
		
		$consulta = DB::raw($consulta);

		$res = DB::update($consulta, $datos);

		if($res)
			return 'Guardado';
		else
			return 'No guardado';

	}



}