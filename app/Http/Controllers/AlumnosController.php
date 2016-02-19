<?php namespace App\Http\Controllers;



use Request;
use DB;
use Hash;

use App\Models\User;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Year;
use App\Models\Nota;
use App\Models\Alumno;
use App\Models\Role;
use App\Models\Matricula;
use App\Models\Unidad;
use App\Models\Subunidad;
use App\Models\Ausencia;
use App\Models\FraseAsignatura;
use App\Models\Asignatura;
use App\Models\NotaComportamiento;
use App\Models\DefinicionComportamiento;

use Carbon\Carbon;


class AlumnosController extends Controller {

	public function __construct()
	{
		
	}

	public function getIndex()
	{
		$user = User::fromToken();

		$previous_year = $user->year - 1;
		$id_previous_year = 0;
		$previous_year = Year::where('year', '=', $previous_year)->first();

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

		return DB::select(DB::raw($consulta), array(
						':id_previous_year'	=>$id_previous_year, 
						':year_id'			=>$user->year_id,
						':year2_id'			=>$user->year_id
				));
	}

	public function getSinMatriculas()
	{
		$user = User::fromToken();

		$consulta = 'SELECT m.id as matricula_id, a.id as alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
				a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
				g.year_id, m.grupo_id, g.nombre as nombre_grupo, g.abrev as abrevgrupo,
				a.foto_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre, 
				m.deleted_at as sin_matricular
			FROM alumnos a 
			INNER JOIN matriculas m on m.alumno_id=a.id and a.deleted_at is null 
			INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:year_id and a.id=m.alumno_id and g.deleted_at is null
			LEFT JOIN images i on i.id=a.foto_id and i.deleted_at is null';

		return DB::select(DB::raw($consulta), array(
						':year_id'			=>$user->year_id
				));
	}


	public function getAlumnosbasico($grupo_id)
	{
		$alumnos = Grupo::find($grupo_id)->alumnos;

		return $alumnos;
	}


	public function checkOrChangeUsername($user_id){

		$user = User::where('username', '=', Request::input('username'))->first();
		//mientras el user exista iteramos y aumentamos i
		if ($user) {

			if ($user->id == $user_id) {
				return;
			}
			
			$username = $user->username;
			$i = 0;
			while(sizeof(User::where('username', '=', $username)->first()) > 0 ){
				$i++;
				$username = $user->username.$i;
			}
			Request::merge(array('username' => $username));
		}
		
	}

	public function postStore()
	{
		$user = User::fromToken();

		$alumno = [];

		try {
			
			$this->sanarInputAlumno();

			$date = Carbon::createFromFormat('Y-m-d', Request::input('fecha_nac'));

			$alumno = new Alumno;
			$alumno->no_matricula	=	Request::input('no_matricula');
			$alumno->nombres	=	Request::input('nombres');
			$alumno->apellidos	=	Request::input('apellidos');
			$alumno->sexo		=	Request::input('sexo');
			#$alumno->user_id	=	Request::input('user_id');
			$alumno->fecha_nac	=	$date->format('Y-m-d');
			$alumno->ciudad_nac	=	Request::input('ciudad_nac');
			$alumno->tipo_doc	=	Request::input('tipo_doc');
			$alumno->documento	=	Request::input('documento');
			$alumno->ciudad_doc	=	Request::input('ciudad_doc');
			$alumno->tipo_sangre	=	Request::input('tipo_sangre')['sangre'];
			$alumno->eps		=	Request::input('eps');
			$alumno->telefono	=	Request::input('telefono');
			$alumno->celular	=	Request::input('celular');
			$alumno->barrio		=	Request::input('barrio');
			$alumno->estrato	=	Request::input('estrato');
			$alumno->ciudad_resid	=	Request::input('ciudad_resid');
			$alumno->religion	=	Request::input('religion');
			$alumno->email		=	Request::input('email');
			$alumno->facebook	=	Request::input('facebook');
			$alumno->foto_id	=	Request::input('foto_id');
			$alumno->pazysalvo	=	Request::input('pazysalvo');
			$alumno->deuda		=	Request::input('deuda');
			$alumno->save();

			$this->sanarInputUser();

			$this->checkOrChangeUsername($alumno->user_id);

			$yearactual = Year::actual();
			$periodo_actual = Periodo::where('actual', true)
									->where('year_id', $yearactual->id)->first();

			$usuario = new User;
			$usuario->username		=	Request::input('username');
			$usuario->password		=	Hash::make(Request::input('password', '123456'));
			$usuario->email			=	Request::input('email2');
			$usuario->is_superuser	=	Request::input('is_superuser', false);
			$usuario->periodo_id	=	$periodo_actual->id;
			$usuario->is_active		=	Request::input('is_active', true);
			$usuario->tipo			=	'Alumno';
			$usuario->save();

			
			$role = Role::where('name', '=', 'Alumno')->get();
			$usuario->attachRole($role[0]);

			$alumno->user_id = $usuario->id;
			$alumno->save();

			$alumno->user = $usuario;

			if (Request::input('grupo')['id']) {
				$grupo_id = Request::input('grupo')['id'];

				$matricula = new Matricula;
				$matricula->alumno_id		=	$alumno->id;
				$matricula->grupo_id		=	$grupo_id;
				$matricula->matriculado		=	true;
				$matricula->save();

				$grupo = Grupo::find($matricula->grupo_id);
				$alumno->grupo = $grupo;
			}


			return $alumno;

		} catch (Exception $e) {
			return abort('400', $alumno);
			//return $e;
		}
		
		 
	}

	public function sanarInputAlumno(){
		if (is_array( Request::input('tipo_sangre') )){
			if (!array_key_exists('sangre', Request::input('tipo_sangre'))) {
				Request::merge(array('tipo_sangre' => array('sangre'=>'')));
			}
		}else{
			Request::merge(array('tipo_sangre' => array('sangre'=>'')));
		}

		if(Request::has('ciudad_nac')){
			if (Request::input('ciudad_nac')['id']) {
				Request::merge(array('ciudad_nac' => Request::input('ciudad_nac')['id'] ) );
			}else{
				Request::merge(array('ciudad_nac' => null) );
			}
		}

		if(Request::has('tipo_doc')){
			if (Request::input('tipo_doc')['id']) {
				Request::merge(array('tipo_doc' => Request::input('tipo_doc')['id'] ) );
			}else{
				Request::merge(array('tipo_doc' => null) );
			}
		}


		if(Request::has('ciudad_doc')){
			if (Request::input('ciudad_doc')['id']) {
				Request::merge(array('ciudad_doc' => Request::input('ciudad_doc')['id'] ) );
			}else{
				Request::merge(array('ciudad_doc' => null) );
			}
		}

		try {
			if (Request::has('foto')){

				if (isset( Request::input('foto')['id'])) {
					Request::merge(array('foto_id' => Request::input('foto')['id'] ) );
				}else if (is_string(Request::input('foto')) ){
					Request::merge(array('foto_id' => Request::input('foto')) );
				}else{
					Request::merge(array('foto_id' => null) );
				}
			}
		} catch (Exception $e) {
			
		}
		
	}

	public function sanarInputUser()
	{
		/*
		//separamos el nombre de la img y la extensión
		$info = explode(".", $file->getClientOriginalName());
		$primer = $info[0];
		*/
		
		if (!Request::input('username')) {
			$dirtyName = Request::input('nombres');
			$name = preg_replace('/\s+/', '', $dirtyName);
			Request::merge(array('username' => $name));
		}

		if (!Request::input('email1')) {

			if (Request::input('email')) {
				Request::merge(array('email2' => Request::input('email') ));
			}else{
				$email = Request::input('username') . '@myvc.com';
				Request::merge(array('email2' => $email));
			}
		}
	}



	public function getShow($id)
	{
		$alumno = Alumno::findOrFail($id);
		if (!is_null($alumno->user_id)){
			$alumno->user = User::findOrFail($alumno->user_id);
		}
		return $alumno;
	}



	public function putUpdate($id)
	{

		$alumno = Alumno::findOrFail($id);

		$this->sanarInputAlumno();

		try {
			$alumno->no_matricula = Request::input('no_matricula');
			$alumno->nombres 	=	Request::input('nombres');
			$alumno->apellidos	=	Request::input('apellidos');
			$alumno->sexo		=	Request::input('sexo', 'M');
			$alumno->fecha_nac	=	Request::input('fecha_nac');
			$alumno->ciudad_nac =	Request::input('ciudad_nac')['id'];
			$alumno->tipo_doc	=	Request::input('tipo_doc')['id'];
			$alumno->documento	=	Request::input('documento');
			$alumno->ciudad_doc	=	Request::input('ciudad_doc')['id'];
			$alumno->tipo_sangre=	Request::input('tipo_sangre')['sangre'];
			$alumno->eps 		=	Request::input('eps');
			$alumno->telefono 	=	Request::input('telefono');
			$alumno->celular 	=	Request::input('celular');
			$alumno->barrio 	=	Request::input('barrio');
			$alumno->estrato 	=	Request::input('estrato');
			$alumno->ciudad_resid =	Request::input('ciudad_resid');
			$alumno->religion	=	Request::input('religion');
			$alumno->email		=	Request::input('email');
			$alumno->facebook	=	Request::input('facebook');
			$alumno->foto_id	=	Request::input('foto_id');
			$alumno->pazysalvo	=	Request::input('pazysalvo', true);
			$alumno->deuda		=	Request::input('deuda');




			if ($alumno->user_id and Request::has('username')) {
				
				$this->sanarInputUser();
				$this->checkOrChangeUsername($alumno->user_id);
				
				$usuario = User::find($alumno->user_id);
				$usuario->username		=	Request::input('username');
				$usuario->email			=	Request::input('email2');
				$usuario->is_superuser	=	Request::input('is_superuser', false);
				$usuario->is_active		=	Request::input('is_active', true);

				if (Request::has('password')) {
					if (Request::input('password') == ""){
						$usuario->password	=	Hash::make(Request::input('password'));
					}
				}

				$usuario->save();

				$alumno->user_id = $usuario->id;
				
				$alumno->save();

				$alumno->user = $usuario;
			}

			if (!$alumno->user_id and Request::has('username')) {
				
				$this->sanarInputUser();
				$this->checkOrChangeUsername($alumno->user_id);

				$yearactual = Year::actual();
				$periodo_actual = Periodo::where('actual', true)
									->where('year_id', $yearactual->id)->first();


				$usuario = new User;
				$usuario->username		=	Request::input('username');
				$usuario->password		=	Hash::make(Request::input('password', '123456'));
				$usuario->email			=	Request::input('email2');
				$usuario->is_superuser	=	Request::input('is_superuser', false);
				$usuario->is_active		=	Request::input('is_active', true);
				$usuario->periodo_id	=	$periodo_actual->id;
				$usuario->save();

				$alumno->user_id = $usuario->id;
				
				$alumno->save();

				$alumno->user = $usuario;
			}



			if (Request::input('grupo')['id']) {
				
				$grupo_id = Request::input('grupo')['id'];

				$matricula = Matricula::matricularUno($alumno->id, $grupo_id);

				$grupo = Grupo::find($matricula->grupo_id);
				$alumno->grupo = $grupo;
			}


			return $alumno;
		} catch (Exception $e) {
			return abort('400', $e);
		}
	}


	public function putDetailedNotasGroup($grupo_id)
	{
		$user = User::fromToken();

		$periodos_a_calcular = Request::input('periodos_a_calcular');

		$boletines = $this->detailedNotasGrupo($grupo_id, $user, '', $periodos_a_calcular, $user->numero_periodo);

		//$grupo->alumnos = $alumnos;
		//$grupo->asignaturas = $asignaturas;
		//return (array)$grupo;

		return $boletines;


	}

	public function getDetailedNotasYear($grupo_id, $periodos_a_calcular='de_usuario')
	{
		$user = User::fromToken();

		$alumnos_response = [];

		$grupo			= Grupo::datos($grupo_id);
		$year			= Year::datos($user->year_id);
		$alumnos		= Grupo::alumnos($grupo_id);

		//return Nota::alumnoAsignaturasPeriodosDetailed($alumno->alumno_id, $user->year_id, $periodos_a_calcular, $user->numero_periodo); // borrar

		foreach ($alumnos as $keyAlum => $alumno) {
			$alumno = Nota::alumnoAsignaturasPeriodosDetailed($alumno->alumno_id, $user->year_id, $periodos_a_calcular, $user->numero_periodo);
			array_push($alumnos_response, $alumno);
		}



		return array($grupo, $year, $alumnos_response);


	}


	public function putDetailedNotas($grupo_id)
	{
		$user = User::fromToken();

		$periodos_a_calcular = 'de_colegio';

		if (Request::has('requested_alumnos')) {
			$periodos_a_calcular = Request::input('periodos_a_calcular');
		}

		$requested_alumnos = '';

		if (Request::has('requested_alumnos')) {
			$requested_alumnos = Request::input('requested_alumnos');
		}

		$boletines = $this->detailedNotasGrupo($grupo_id, $user, $requested_alumnos, $periodos_a_calcular, $user->numero_periodo);

		//$grupo->alumnos = $alumnos;
		//$grupo->asignaturas = $asignaturas;
		//return (array)$grupo;

		return $boletines;


	}

	public function detailedNotasGrupo($grupo_id, $user, $requested_alumnos='', $periodos_a_calcular='de_usuario', $periodo_usuario=0)
	{
		
		$grupo			= Grupo::datos($grupo_id);
		$year			= Year::datos($user->year_id);
		$alumnos		= Grupo::alumnos($grupo_id, $requested_alumnos);

		$year->periodos = Periodo::hastaPeriodo($user->year_id, $periodos_a_calcular, $periodo_usuario);

		$grupo->cantidad_alumnos = count($alumnos);

		$response_alumnos = [];
		

		foreach ($alumnos as $alumno) {

			// Todas las materias con sus unidades y subunides
			$this->allNotasAlumno($alumno, $grupo_id, $user->periodo_id, true);


			$asignaturas_perdidas = $this->asignaturasPerdidasDeAlumno($alumno, $grupo_id, $user->year_id, $periodos_a_calcular, $periodo_usuario);

			if (count($asignaturas_perdidas) > 0) {
				
				$alumno->asignaturas_perdidas = $asignaturas_perdidas;
				$alumno->notas_perdidas_year = 0;
				$alumno->periodos_con_perdidas = Periodo::hastaPeriodo($user->year_id, $periodos_a_calcular, $periodo_usuario);

				foreach ($alumno->periodos_con_perdidas as $keyPerA => $periodoAlone) {

					$periodoAlone->cant_perdidas = 0;
					
					foreach ($alumno->asignaturas_perdidas as $keyAsig => $asignatura_perdida) {

						foreach ($asignatura_perdida->periodos as $keyPer => $periodo) {

							if ($periodoAlone->periodo_id == $periodo->periodo_id) {
								if ($periodo->id == $periodoAlone->id) {
									$periodoAlone->cant_perdidas += $periodo->cantNotasPerdidas;
								}
								
							}
						}
					}

					$alumno->notas_perdidas_year += $periodoAlone->cant_perdidas;
					
				}
			}
		}


		foreach ($alumnos as $alumno) {
			
			$alumno->puesto = Nota::puestoAlumno($alumno->promedio, $alumnos);
			
			if ($requested_alumnos == '') {

				array_push($response_alumnos, $alumno);

			}else{

				foreach ($requested_alumnos as $req_alumno) {
					
					if ($req_alumno['alumno_id'] == $alumno->alumno_id) {
						array_push($response_alumnos, $alumno);
					}
				}
			}
			

		}

		return array($grupo, $year, $response_alumnos);
	}

	public function allNotasAlumno(&$alumno, $grupo_id, $periodo_id, $comport_and_frases=false)
	{


		$asignaturas	= Grupo::detailed_materias($grupo_id);

		foreach ($asignaturas as $asignatura) {
			$asignatura->unidades = Unidad::deAsignatura($asignatura->asignatura_id, $periodo_id);

			foreach ($asignatura->unidades as $unidad) {
				$unidad->subunidades = Subunidad::deUnidad($unidad->unidad_id);
			}
		}

		$alumno->asignaturas = $asignaturas;

		$sumatoria_asignaturas = 0;

		foreach ($alumno->asignaturas as $asignatura) {

			if ($comport_and_frases) {
				$asignatura->ausencias	= Ausencia::deAlumno($asignatura->asignatura_id, $alumno->alumno_id, $periodo_id);
				$asignatura->frases		= FraseAsignatura::deAlumno($asignatura->asignatura_id, $alumno->alumno_id, $periodo_id);
			}

			Asignatura::calculoAlumnoNotas($asignatura, $alumno->alumno_id);

			$sumatoria_asignaturas += $asignatura->nota_asignatura; // Para sacar promedio del periodo


			// SUMAR AUSENCIAS Y TARDANZAS
			if ($comport_and_frases) {
				$cantAus = 0;
				$cantTar = 0;
				foreach ($asignatura->ausencias as $ausencia) {
					$cantAus += (int)$ausencia->cantidad_ausencia;
					$cantTar += (int)$ausencia->cantidad_tardanza;
				}

				$asignatura->total_ausencias = $cantAus;
				$asignatura->total_tardanzas = $cantTar;
			}

		}
		try {
			$alumno->promedio = $sumatoria_asignaturas / count($alumno->asignaturas);
		} catch (Exception $e) {
			$alumno->promedio = 0;
		}



		// COMPORTAMIENTO Y SUS FRASES
		if ($comport_and_frases) {

			$comportamiento = NotaComportamiento::where('alumno_id', '=', $alumno->alumno_id)
												->where('periodo_id', '=', $periodo_id)
												->first();

			$alumno->comportamiento = $comportamiento;
			$definiciones = [];

			if ($comportamiento) {
				$definiciones = DefinicionComportamiento::frases($comportamiento->id);
				$alumno->comportamiento->definiciones = $definiciones;
			}


		}
		


		return $alumno;
	}


	public function asignaturasPerdidasDeAlumno($alumno, $grupo_id, $year_id, $periodos_a_calcular, $periodo_usuario)
	{
		$asignaturas	= Grupo::detailed_materias($grupo_id);


		foreach ($asignaturas as $keyAsig => $asignatura) {
			
			$periodos = Periodo::hastaPeriodo($year_id, $periodos_a_calcular, $periodo_usuario);

			$asignatura->cantTotal = 0;

			foreach ($periodos as $keyPer => $periodo) {

				$periodo->cantNotasPerdidas = 0;
				$periodo->unidades = Unidad::deAsignatura($asignatura->asignatura_id, $periodo->id);


				foreach ($periodo->unidades as $keyUni => $unidad) {
					
					$subunidades = Subunidad::perdidasDeUnidad($unidad->unidad_id, $alumno->alumno_id);
					
					if (count($subunidades) > 0) {
						$unidad->subunidades = $subunidades;
						$periodo->cantNotasPerdidas += count($subunidades);
					}else{
						$uniTemp = $periodo->unidades;
						unset($uniTemp[$keyUni]);
						$periodo->unidades = $uniTemp;
					}
				}
				#$periodo->unidades = $unidades;

				$asignatura->cantTotal += $periodo->cantNotasPerdidas;
				
				if (count($periodo->unidades) > 0) {
					#$periodo->unidades = $unidades;
				}else{
					unset($periodos[$keyPer]);
				}
				
				
			}

			if (count($periodos) > 0) {
				$asignatura->periodos = $periodos;
			}else{
				unset($asignaturas[$keyAsig]);
			}

			$hasPeriodosConPerdidas = false;

			foreach ($periodos as $keyPer => $periodo) {
				if (count($periodo->unidades) > 0) {
					$hasPeriodosConPerdidas = true;
				}
			}

			if (!$hasPeriodosConPerdidas) {
				unset($asignaturas[$keyAsig]);
			}

		}

		return $asignaturas;

	}

	public function periodosPerdidosDeAlumno($alumno, $grupo_id, $year_id, $periodos)
	{
		//$periodos = Periodo::where('year_id', '=', $year_id)->get();

		foreach ($periodos as $key => $periodo) {
			$periodo->asignaturas = $this->asignaturasPerdidasDeAlumnoPorPeriodo($alumno->alumno_id, $grupo_id, $periodo->id);

			if (count($periodo->asignaturas)==0) {
				unset($periodos[$key]);
			}
		}
	}

	public function asignaturasPerdidasDeAlumnoPorPeriodo($alumno_id, $grupo_id, $periodo_id)
	{


		$asignaturas	= Grupo::detailed_materias($grupo_id);

		foreach ($asignaturas as $keyAsig => $asignatura) {

			$asignatura->unidades = Unidad::deAsignatura($asignatura->asignatura_id, $periodo_id);

			foreach ($asignatura->unidades as $keyUni => $unidad) {
				$unidad->subunidades = Subunidad::perdidasDeUnidad($unidad->unidad_id, $alumno_id);

				if (count($unidad->subunidades) == 0) {
					unset($asignatura->unidades[$keyUni]);
				}
			}
			if (count($asignatura->unidades) == 0) {
				unset($asignaturas[$keyAsig]);
			}
		}


		return $asignaturas;
	}


	public function deleteDestroy($id)
	{
		$alumno = Alumno::find($id);
		//Alumno::destroy($id);
		//$alumno->restore();
		//$queries = DB::getQueryLog();
		//$last_query = end($queries);
		//return $last_query;

		if ($alumno) {
			$alumno->delete();
		}else{
			return abort(400, 'Alumno no existe o está en Papelera.');
		}
		return $alumno;
	
	}	

	public function deleteForcedelete($id)
	{
		$alumno = Alumno::onlyTrashed()->findOrFail($id);
		
		if ($alumno) {
			$alumno->forceDelete();
		}else{
			return abort(400, 'Alumno no encontrado en la Papelera.');
		}
		return $alumno;
	
	}

	public function putRestore($id)
	{
		$alumno = Alumno::onlyTrashed()->findOrFail($id);

		if ($alumno) {
			$alumno->restore();
		}else{
			return abort(400, 'Alumno no encontrado en la Papelera.');
		}
		return $alumno;
	}


	public function getTrashed()
	{
		$user = User::fromToken();
		$previous_year = $user->year - 1;
		$id_previous_year = 0;
		$previous_year = Year::where('year', '=', $previous_year)->first();


		$consulta = 'SELECT m2.matricula_id, a.id as alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
				a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
				m2.year_id, m2.grupo_id, m2.nombregrupo, m2.abrevgrupo, IFNULL(m2.actual, -1) as currentyear,
				u.username, u.is_superuser, u.is_active
			FROM alumnos a left join 
				(select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 0 as actual
				from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:id_previous_year
				and m.alumno_id NOT IN 
					(select m.alumno_id
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:year_id)
					union
					select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 1 AS actual
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:year2_id
				)m2 on a.id=m2.alumno_id
			left join users u on u.id=a.user_id where a.deleted_at is not null';

		return DB::select(DB::raw($consulta), array(
						':id_previous_year'	=>$id_previous_year, 
						':year_id'			=>$user->year_id,
						':year2_id'			=>$user->year_id
				));
	}

}