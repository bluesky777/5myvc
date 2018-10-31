<?php namespace App\Http\Controllers\Alumnos;



use Request;
use DB;
use Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Year;
use App\Models\Periodo;



class GuardarAlumno {


	public function valor($user, $propiedad, $valor, $user_id, $year_id)
	{

		$consulta 	= '';
		$datos 		= [];
		$now 		= Carbon::now('America/Bogota');
		$alumno_id 	= Request::input('alumno_id');

		if ($propiedad == 'fecha_nac' || $propiedad == 'fecha_retiro' || $propiedad == 'prematriculado')
			$valor = Carbon::parse($valor);

		switch ($propiedad) {
			case 'username':
				$consulta 	= 'UPDATE users SET username=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:user_id';
				$datos 		= [ ':valor' => $valor, ':modificador' => $user_id, ':fecha' => $now, ':user_id' => $alumno->user_id ];
			break;
			
			case 'nuevo':
			case 'fecha_pension':
			case 'fecha_retiro':
			case 'fecha_matricula':
			case 'razon_retiro':
			case 'repitente':
			case 'prematriculado':
			
				$consulta 	= 'SELECT a.id, a.user_id, g.id as grupo_id, g.titular_id, m.id as matricula_id FROM alumnos a
								INNER JOIN matriculas m ON m.alumno_id=a.id
								INNER JOIN grupos g ON g.id=m.grupo_id AND g.year_id=?
								WHERE a.id=?'; // Tengo confusión con INNER o LEFT grupos
				$alumno 	= DB::select($consulta, [ $year_id, $alumno_id ]);
				
				if (count($alumno)>0) {
					$alumno = $alumno[0];
				}else{
					return response()->json([ 'No encontrado'=> false, 'msg'=> 'Alumno no encontrado' ], 400);
				}

				$consulta = 'UPDATE matriculas SET '.$propiedad.'=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:matricula_id';
				$datos 		= [
					':valor'		=> $valor, 
					':modificador'	=> $user_id, 
					':fecha' 		=> $now,
					':matricula_id'	=> $alumno->matricula_id
				];
			break;
			
			default:
				
				$consulta = 'UPDATE alumnos SET '.$propiedad.'=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:alumno_id';
				$datos 		= [
					':valor'		=> $valor, 
					':modificador'	=> $user_id, 
					':fecha' 		=> $now,
					':alumno_id'	=> $alumno_id
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