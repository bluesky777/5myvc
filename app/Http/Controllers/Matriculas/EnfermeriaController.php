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
        
        $consulta           = 'SELECT * FROM antecedentes WHERE alumno_id=?';
        $antecedentes      = DB::select($consulta, [Request::input('alumno_id')]);
        
        
        return ['antecedentes'=>$antecedentes];
	}
	




}