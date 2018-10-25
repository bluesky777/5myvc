<?php namespace App\Http\Controllers\Matriculas;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\Matricula;
use App\Models\Acudiente;
use Carbon\Carbon;

use App\Events\MatriculasEvent;
use \Log;


class RequisitosController extends Controller {


	public $user;
	
	public function __construct()
	{
		$this->user = User::fromToken();
		if($this->user->roles[0]->name != 'Admin'){
			return 'No tienes permiso';
		}
	}
	

	public function putIndex()
	{
        
        $consulta   = 'SELECT id, year, actual, abrev_colegio FROM years WHERE deleted_at is null ORDER BY year desc';
        $years      = DB::select($consulta);
        
        for ($i=0; $i < count($years); $i++) { 
           
            $consulta = 'SELECT * FROM requisitos_matricula WHERE year_id=? and deleted_at is null';
            $years[$i]->requisitos = DB::select($consulta, [$years[$i]->id]);
        }
        
        return $years;
	}
	

	public function postStore()
	{
        $requ       = Request::input('requisito');
        $descrip    = Request::input('descripcion');
        $year_id    = Request::input('year_id');
        $now 		= Carbon::now('America/Bogota');
        
        $consulta = 'INSERT INTO requisitos_matricula(requisito, descripcion, updated_by, created_at, updated_at, year_id) 
            VALUES(?,?,?,?,?,?)';
        DB::insert($consulta, [$requ, $descrip, $this->user->user_id, $now, $now, $year_id]);
        
        $consulta = 'SELECT * FROM requisitos_matricula WHERE id=?';
        $requisito = DB::select($consulta, [ DB::getPdo()->lastInsertId() ] )[0];
        
        return ['requisito' => $requisito];
	}
	


	public function putUpdate()
	{
        $id         = Request::input('id');
        $requ       = Request::input('requisito');
        $descrip    = Request::input('descripcion');
        $now 		= Carbon::now('America/Bogota');
        
        $consulta = 'UPDATE requisitos_matricula SET requisito=?, descripcion=?, updated_by=?, updated_at=? WHERE id=?';
        DB::select($consulta, [$requ, $descrip, $this->user->user_id, $now, $id]);
        
        return 'Actualizado';
	}
	

	public function deleteDestroy($id)
	{
        $now 		= Carbon::now('America/Bogota');
        $consulta   = 'UPDATE requisitos_matricula SET deleted_at=? WHERE id=?';
		DB::update($consulta, [$now, $id]);

		return 'Eliminado';
	}







}