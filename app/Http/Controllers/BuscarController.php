<?php namespace App\Http\Controllers;

use Request;
use DB;

use App\Models\User;
use App\Models\Matricula;


class BuscarController extends Controller {




	public function putPorNombre()
	{
		$texto_a_buscar = Request::input('texto_a_buscar');

		$consulta = "SELECT a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, a.created_by, a.updated_by, a.deleted_by, a.deleted_at, a.created_at, a.updated_at,
						a.foto_id, IFNULL(i.nombre, IF(a.sexo='F','default_female.png', 'default_male.png')) as foto_nombre
					FROM alumnos a
					left join images i on i.id=a.foto_id and i.deleted_at is null
					WHERE a.nombres like '%$texto_a_buscar%'";

		$res = DB::select(DB::raw($consulta));
		return $res;
	}




}