<?php namespace App\Http\Controllers\Disciplina;

use Request;
use DB;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Disciplina\DisciplinaController;
use App\Models\User;
use App\Models\NotaComportamiento;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\Frase;
use App\Models\Year;

use Carbon\Carbon;


class ComportamientoController extends Controller {

	public function getIndex()
	{
		$user = User::fromToken();
		return NotaComportamiento::all();
	}



	public function putSituacionesPorGrupos()
	{
		$user 		= User::fromToken();


		$consulta = 'SELECT g.id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
				p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
				g.created_at, g.updated_at, gra.nombre as nombre_grado 
			from grupos g
			inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id
			left join profesores p on p.id=g.titular_id and p.deleted_at is null
			where g.deleted_at is null
			order by g.orden';

		$res['grupos'] 	= DB::select($consulta, [':year_id'=>$user->year_id] );
		$discCtrl 		= new DisciplinaController;
			
		for ($i=0; $i < count($res['grupos']); $i++) { 

			$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
					a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
					m.grupo_id, u.username, 
					u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
					a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre
				FROM alumnos a
				INNER JOIN matriculas m ON a.id=m.alumno_id and a.deleted_at is null and m.deleted_at is null and (m.estado="ASIS" or m.estado="PREM" or m.estado="MATR")
				left join users u on a.user_id=u.id and u.deleted_at is null
				left join images i on i.id=u.imagen_id and i.deleted_at is null
				left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
				WHERE m.grupo_id=?';

			$alumnos 		= DB::select($consulta, [$res['grupos'][$i]->id]);
			$alumnos_res 	= [];

			for ($j=0; $j < count($alumnos); $j++) { 
				
				$discCtrl->datosAlumno($alumnos[$j], $user->year_id);
				
				if (count($alumnos[$j]->periodo1) > 0 || count($alumnos[$j]->periodo2) > 0 || count($alumnos[$j]->periodo3) > 0 || count($alumnos[$j]->periodo4) > 0) {
					array_push($alumnos_res, $alumnos[$j]);
				}

			}

			$res['grupos'][$i]->alumnos = $alumnos_res;
			
		}

		return $res;
	}


	/*
	public function putCrear()
	{
		$user 	= User::fromToken();
		$now 	= Carbon::now('America/Bogota');

		DB::insert('INSERT INTO nota_comportamiento (alumno_id, periodo_id, nota, created_at, updated_at) VALUES (?,?,?,?,?)', 
			[ Request::input('alumno_id'), Request::input('periodo_id'), Request::input('nota'), $now, $now ]);

		$last_id = DB::getPdo()->lastInsertId();


		$consulta = 'SELECT n.*, p.nombres, p.apellidos, p.sexo, p.id as titular_id,
				p.foto_id, IFNULL(i.nombre, IF(p.sexo="F","default_female.png", "default_male.png")) as foto_nombre
			FROM nota_comportamiento n
			inner join matriculas m on m.alumno_id=n.alumno_id and m.deleted_at is null
			inner join grupos g on g.id=m.grupo_id and g.deleted_at is null and g.year_id=:year_id
			inner join profesores p on p.id=g.titular_id and p.deleted_at is null 
			left join images i on i.id=p.foto_id and i.deleted_at is null
			where n.alumno_id=:alumno_id and n.periodo_id=:periodo_id and n.deleted_at is null';
			
		$nota_comportamiento = DB::select($consulta, [
			':year_id'		=>Request::input('year_id'), 
			':alumno_id'	=>Request::input('alumno_id'), 
			':periodo_id'	=>Request::input('periodo_id')
		]);

		if(count($nota_comportamiento) > 0){
			$nota_comportamiento = $nota_comportamiento[0];
		}else{
			$nota_comportamiento = [];
		}
		return ['nota_comport' => $nota_comportamiento];
	}


	public function deleteDestroy($id)
	{
		$nota = NotaComportamiento::findOrFail($id);
		$nota->delete();

		return $nota;
	}
	*/

}