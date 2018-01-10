<?php namespace App\Http\Controllers\Disciplina;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\Year;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Asignatura;
use App\Models\Subunidad;
use App\Models\Profesor;
use App\Models\Alumno;


class DisciplinaController extends Controller {

	public $user;

	public function __construct()
	{
		$this->user = User::fromToken();
	}



	public function getGrupoDisciplina()
	{
		$periodo_a_calcular 	= (int)Request::input('periodo_a_calcular');
		$profesor_id 			= Request::input('profesor_id');
		//$periodos 				= Periodo::hastaPeriodo($user->year_id, $periodo_a_calcular, $user->numero_periodo);
		

		$consulta = 'SELECT g.id as grupo_id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
			p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
			g.created_at, g.updated_at, gra.nombre as nombre_grado 
			from grupos g
			inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
			left join profesores p on p.id=g.titular_id
			where g.deleted_at is null
			order by g.orden';

		$grupos_all 		= DB::select($consulta, [':year_id' => $this->user->year_id]);



		return $grupos_all;
	}





}