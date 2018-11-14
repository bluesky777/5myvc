<?php namespace App\Http\Controllers\Matriculas;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use Carbon\Carbon;

use \Log;


class EnfermeriaController extends Controller {


	public $user;
	
	public function __construct()
	{
		$this->user = User::fromToken();
	}
	

	public function putDatos()
	{
		$now 				= Carbon::now('America/Bogota');
		
        $consulta          = 'SELECT * FROM antecedentes WHERE alumno_id=?';
        $antecedentes      = DB::select($consulta, [Request::input('alumno_id')]);
		
		if (count($antecedentes) == 0) {
			$consulta          = 'INSERT INTO antecedentes(alumno_id, updated_by, created_at, updated_at) VALUES(?,?,?,?)';
			$antecedentes      = DB::select($consulta, [Request::input('alumno_id'), $this->user->user_id, $now, $now ]);
			
			$consulta          = 'SELECT * FROM antecedentes WHERE alumno_id=?';
			$antecedentes      = DB::select($consulta, [Request::input('alumno_id')]);
			
		}
        
        return ['antecedentes'=>$antecedentes];
	}
	


	public function putGuardarValor()
	{
		if($this->user->roles[0]->name == 'Admin' || $this->user->roles[0]->name == 'Enfermero'){
			$now 				= Carbon::now('America/Bogota');
			
			$consulta 			= 'SELECT * FROM antecedentes WHERE alumno_id=?';
			$antecedentes		= DB::select($consulta, [Request::input('alumno_id')]);
			
			if (count($antecedentes) == 0) {
				$consulta          = 'INSERT INTO antecedentes(alumno_id, updated_by, created_at, updated_at) VALUES(?,?,?,?)';
				$antecedentes      = DB::select($consulta, [Request::input('alumno_id'), $this->user->user_id, $now, $now ]);
				
				$consulta          = 'SELECT * FROM antecedentes WHERE alumno_id=?';
				$antecedentes      = DB::select($consulta, [Request::input('alumno_id')]);
				
			}
			return [ 'Cambios guardados' ];
		}else{
			return abort(401, 'No puedes cambiar');
		}
			
	}
	




}