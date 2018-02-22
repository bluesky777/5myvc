<?php namespace App\Http\Controllers\Alumnos;


use DB;
use Request;
use Excel;
use Hash;

use App\Models\User;
use App\Models\Role;
use App\Models\Matricula;
use App\Models\Year;
use App\Models\Alumno;
use App\Models\Debugging;
use App\Http\Controllers\Alumnos\OperacionesAlumnos;
use App\Http\Controllers\Alumnos\Definitivas;

use App\Http\Controllers\Alumnos\Solicitudes;
use App\Http\Controllers\Controller;


class ImportarController extends Controller {

	public function getIndex()
	{

		$rr = Excel::load('app/Http/Controllers/Alumnos/archivos/alumnos.xls', function($reader) {

			$results = $reader->all();
			
			
			for ($i=0; $i < count($results); $i++) { 
				
				
				$abrev 		= $results[$i]->getTitle();
				$consulta 	= 'SELECT * FROM grupos WHERE abrev=?';
				$grupo 		= DB::select($consulta, [$abrev])[0];
				
				
				
				for ($f=0; $f < count($results[$i]); $f++) { 
					
					$alumno_row = $results[$i][$f];
					
					$alumno = new Alumno;
					$alumno->nombres    = $alumno_row->nombres;
					$alumno->apellidos  = $alumno_row->apellidos;
					$alumno->sexo       = $alumno_row->sexo;
					$alumno->save();
					
					
					$consulta 	= 'SELECT * FROM years WHERE actual=1';
					$year 		= DB::select($consulta)[0];
					
					
					$opera = new OperacionesAlumnos();
					
					$usuario = new User;
					$usuario->username		=	$opera->username_no_repetido($alumno->nombres);
					$usuario->password		=	Hash::make('123456');
					$usuario->sexo			=	$alumno_row->sexo;
					$usuario->is_superuser	=	false;
					$usuario->periodo_id	=	1; // Verificar que haya un periodo cod 1
					$usuario->is_active		=	true;
					$usuario->tipo			=	'Alumno';
					$usuario->save();

					
					$role = Role::where('name', 'Alumno')->get();
					$usuario->attachRole($role[0]);

					$alumno->user_id = $usuario->id;
					$alumno->save();


					$matricula = new Matricula;
					$matricula->alumno_id		=	$alumno->id;
					$matricula->grupo_id		=	$grupo->id;
					$matricula->estado			=	"MATR";
					$matricula->save();

				
				}
			}
		});
		
		return (array)$rr;
	}


}

