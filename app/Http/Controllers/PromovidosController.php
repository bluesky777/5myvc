<?php namespace App\Http\Controllers;



use Request;
use DB;
use Hash;

use App\Models\User;
use App\Models\Year;
use App\Models\Nota;
use App\Models\Alumno;
use App\Models\Role;
use App\Models\Matricula;
use App\Models\Asignatura;
use \Log;

use Carbon\Carbon;


class PromovidosController extends Controller {

	public $user;

	public function __construct()
	{
		$this->user = User::fromToken();
	}

	public function putCalcularGrupo()
	{
		$previous_year 		= $this->user->year - 1;
		$id_previous_year 	= 0;
		$previous_year 		= Year::where('year', $previous_year)->first();

		if ($previous_year) {
			$id_previous_year = $previous_year->id;
		}

		$consulta = 'SELECT m2.matricula_id, a.id as alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
				a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion, a.pazysalvo, a.deuda,
				m2.year_id, m2.grupo_id, m2.nombregrupo, m2.abrevgrupo, IFNULL(m2.actual, -1) as currentyear,
				u.username, u.is_superuser, u.is_active
			FROM alumnos a left join 
				(select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 0 as actual
				from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:id_previous_year
				and m.alumno_id NOT IN 
					(select m.alumno_id
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:year_id and m.deleted_at is null )
					union
					select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 1 AS actual
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:year2_id and m.deleted_at is null 
				)m2 on a.id=m2.alumno_id
			left join users u on u.id=a.user_id where a.deleted_at is null';

		return DB::select($consulta, [
						':id_previous_year'	=>$id_previous_year, 
						':year_id'			=>$this->user->year_id,
						':year2_id'			=>$this->user->year_id
				]);
	}



}