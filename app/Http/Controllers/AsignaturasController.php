<?php namespace App\Http\Controllers;


use DB;
use Request;

use App\Models\User;
use App\Models\Year;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Unidad;

use App\Http\Controllers\Alumnos\Solicitudes;


class AsignaturasController extends Controller {

	public function getIndex()
	{
		$user = User::fromToken();

		$consulta = 'SELECT a.id, a.materia_id, a.grupo_id, a.profesor_id, a.creditos, a.orden,
						a.created_by, a.updated_by, a.created_at, a.updated_at
					FROM asignaturas a
					inner join grupos g on g.id=a.grupo_id and g.year_id=?
					where a.deleted_at is null
					order by g.orden, a.orden';

		$asignaturas = DB::select($consulta, array($user->year_id));
		return $asignaturas;
	}

	
	public function putDatosAsignaturas()
	{
		$user = User::fromToken();

		$consulta 	= 'SELECT * FROM materias WHERE deleted_at is null';
		$materias 	= DB::select($consulta);
		
		$consulta = 'SELECT g.id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
			p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo, g.caritas, 
			g.created_at, g.updated_at, gra.nombre as nombre_grado 
			from grupos g
			inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
			left join profesores p on p.id=g.titular_id
			where g.deleted_at is null
			order by g.orden';

		$grupos 	= DB::select($consulta, [':year_id'=>$user->year_id]);
		
		$profesores = Profesor::contratos($user->year_id);
		
		return [ 'materias' => $materias, 'grupos' => $grupos, 'profesores' => $profesores ];
	}

	
	
	public function postCopiar()
	{
		$user = User::fromToken();

		$consulta 		= 'SELECT * FROM asignaturas WHERE deleted_at is null and grupo_id=?';
		$asignaturas 	= DB::select($consulta, [Request::input('grupo_id_origen')]);
		
		for ($i=0; $i < count($asignaturas); $i++) { 

			$consulta 		= 'INSERT INTO asignaturas(materia_id, grupo_id, profesor_id, nuevo_responsable_id, creditos, orden) VALUES(?,?,?, ?,?,?)';
			DB::insert($consulta, [ $asignaturas[$i]->materia_id, Request::input('grupo_id_destino'), $asignaturas[$i]->profesor_id, $asignaturas[$i]->nuevo_responsable_id, $asignaturas[$i]->creditos, $asignaturas[$i]->orden ]);
			
		}
		
		
		return 'Asignaturas copiadas';
	}

	
	public function postIndex()
	{
		
		$this->fixInputs();

		$asignatura = new Asignatura;
		$asignatura->materia_id		=	Request::input('materia_id');
		$asignatura->grupo_id		=	Request::input('grupo_id');
		$asignatura->profesor_id	=	Request::input('profesor_id');
		$asignatura->creditos		=	Request::input('creditos');
		$asignatura->orden			=	Request::input('orden');
		$asignatura->save();

		return $asignatura;
	}

	public function getShow($asignatura_id)
	{
		$user = User::fromToken();
		$asignatura = Asignatura::detallada($asignatura_id, $user->year_id);
		return $asignatura;
	}

	public function putUpdate($id)
	{
		$asignatura = Asignatura::findOrFail($id);

		$this->fixInputs();

		$asignatura->materia_id		=	Request::input('materia_id');
		$asignatura->grupo_id		=	Request::input('grupo_id');
		$asignatura->profesor_id	=	Request::input('profesor_id', $asignatura->profesor_id);
		$asignatura->creditos		=	Request::input('creditos');
		$asignatura->orden			=	Request::input('orden');

		$asignatura->save();
		return $asignatura;
	}

	private function fixInputs()
	{
		if (!Request::input('profesor_id') and Request::input('profesor')['profesor_id']) {
			Request::merge(array('profesor_id' => Request::input('profesor')['profesor_id'] ) );
		}

		if (!Request::input('grupo_id') and Request::input('grupo')['id']) {
			Request::merge(array('grupo_id' => Request::input('grupo')['id'] ) );
		}

		if (!Request::input('materia_id') and Request::input('materia')['id']) {
			Request::merge(array('materia_id' => Request::input('materia')['id'] ) );
		}
	}


	public function getListasignaturas($persona_id='')
	{
		$user = User::fromToken();
		$info_profesor = false;

		if ($persona_id=='') {
			$persona_id = $user->persona_id;
		}else{
			$info_profesor = Profesor::detallado($persona_id);
		}

		$consulta 		= '';
		$asignaturas 	= '';
		$pedidos 		= [];

		switch ($user->tipo) {
			case 'Profesor' or 'Usuario':
				$asignaturas = Profesor::asignaturas($user->year_id, $persona_id);

				foreach ($asignaturas as $asignatura) {

					$asignatura->unidades = Unidad::informacionAsignatura($asignatura->asignatura_id, $user->periodo_id);
					
				}

				if ($user->tipo == 'Profesor') {
					$solicitudes 	= new Solicitudes();
					$pedidos 		= $solicitudes->asignaturas_a_cambiar_de_profesor($user->user_id, $user->year_id);
					$res['pedidos']	= $pedidos;

					$consulta = 'SELECT g.id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
						p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
						g.created_at, g.updated_at, gra.nombre as nombre_grado 
						from grupos g
						inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
						left join profesores p on p.id=g.titular_id
						where g.deleted_at is null
						order by g.orden';

					$res['grupos'] = DB::select($consulta, [':year_id'=>$user->year_id] );


					$consulta = 'SELECT * from materias	where deleted_at is null order by materia';
					$res['materias'] = DB::select($consulta);


				}
				
				break;

			case 'Alumno':
				$consulta = 'SELECT a.id as asignatura_id, a.grupo_id, a.profesor_id, a.creditos, a.orden,
							m.materia, m.alias as alias_materia, g.nombre as nombre_grupo, g.abrev as abrev_grupo, g.titular_id, g.caritas
						FROM asignaturas a
						inner join materias m on m.id=a.materia_id and m.deleted_at is null
						inner join grupos g on g.id=a.grupo_id and g.year_id=:year_id and g.deleted_at is null
						where a.profesor_id=:profesor_id and a.deleted_at is null
						order by g.orden, a.orden, a.id';

				$asignaturas = DB::select($consulta, [':year_id' => $user->year_id, ':profesor_id' => $persona_id]);
				
				break;
			
			default:
				# code...
				break;
		}

		$res['asignaturas'] = $asignaturas;

		if ($info_profesor) {
			$res['info_profesor'] = $info_profesor;
		}

		
		$consulta = 'SELECT g.id, g.nombre, g.abrev, g.orden, g.grado_id, g.year_id, g.titular_id,
			p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
			g.created_at, g.updated_at, gra.nombre as nombre_grado 
			from grupos g
			inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
			inner join profesores p on p.id=g.titular_id and g.titular_id = :profesor_id
			where g.deleted_at is null
			order by g.orden';

		$grados = DB::select($consulta, array(':year_id'=>$user->year_id, ':profesor_id' => $persona_id));

		$res['grados_comp'] = $grados;


		return $res;
	}



	// Solo las asignaturas para el popup del menú "notas" de los profesores
	public function getListasignaturasAlone()
	{
		$user = User::fromToken();

		$persona_id = $user->persona_id;

		$consulta = '';
		$asignaturas = '';
		$asignaturas = Profesor::asignaturas($user->year_id, $persona_id);


		return $asignaturas;
	}

	public function getListasignaturasyear($profesor_id, $periodo_id)
	{
		$user = User::fromToken();

		$year = Year::de_un_periodo($periodo_id);

		$asignaturas = Profesor::asignaturas($year->id, $profesor_id);

		foreach($asignaturas as $asignatura) {

			$asignatura->unidades = Unidad::informacionAsignatura($asignatura->asignatura_id, $periodo_id);
			
		}
		

		return $asignaturas;
	}


	public function deleteDestroy($id)
	{
		$asignatura = Asignatura::findOrFail($id);
		$asignatura->delete();

		return $asignatura;
	}

}

