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
use App\Models\ImageModel;

use Carbon\Carbon;

use App\Http\Controllers\Alumnos\GuardarAlumno;


class AlumnosController extends Controller {

	public $user;

	public function __construct()
	{
		$this->user = User::fromToken();
	}

	public function getIndex()
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

	public function getSinMatriculas()
	{
		$consulta = 'SELECT m.id as matricula_id, a.id as alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
				a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
				g.year_id, m.grupo_id, g.nombre as nombre_grupo, g.abrev as abrevgrupo,
				a.foto_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
				m.estado 
			FROM alumnos a 
			INNER JOIN matriculas m on m.alumno_id=a.id and a.deleted_at is null and m.deleted_at is null 
			INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=:year_id and a.id=m.alumno_id and g.deleted_at is null
			LEFT JOIN images i on i.id=a.foto_id and i.deleted_at is null';

		return DB::select(DB::raw($consulta), array(
						':year_id'			=>$this->user->year_id
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


	public function putEpsCheck()
	{
		$texto = Request::input('texto');
		$consulta = 'SELECT distinct eps FROM alumnos WHERE eps like :texto;';
		
		$res = DB::select($consulta, [':texto' => '%'.$texto.'%']);
		return [ 'eps' => $res ];
	}


	public function postStore()
	{

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
			$alumno->pazysalvo	=	Request::input('pazysalvo');
			$alumno->deuda		=	Request::input('deuda');
			$alumno->updated_by	=	$this->user->user_id;
			$alumno->save();

			$this->sanarInputUser();

			$this->checkOrChangeUsername($alumno->user_id);

			$yearactual = Year::actual();
			$periodo_actual = Periodo::where('actual', true)
									->where('year_id', $yearactual->id)->first();

			if (!is_object($periodo_actual)) {
				$periodo_actual = Periodo::where('year_id', $yearactual->id)->first();
				$periodo_actual->actual 	= true;
				$periodo_actual->updated_by = $this->user->user_id;
				$periodo_actual->save();
			}

			$usuario = new User;
			$usuario->username		=	Request::input('username');
			$usuario->password		=	Hash::make(Request::input('password', '123456'));
			$usuario->email			=	Request::input('email2');
			$usuario->sexo			=	Request::input('sexo');
			$usuario->is_superuser	=	Request::input('is_superuser', false);
			$usuario->periodo_id	=	$periodo_actual->id;
			$usuario->is_active		=	Request::input('is_active', true);
			$usuario->tipo			=	'Alumno';
			$usuario->updated_by	=	$this->user->user_id;
			$usuario->save();

			
			$role = Role::where('name', 'Alumno')->get();
			$usuario->attachRole($role[0]);

			$alumno->user_id = $usuario->id;
			$alumno->save();

			$alumno->user = $usuario;

			if (Request::input('grupo')['id']) {
				$grupo_id = Request::input('grupo')['id'];

				$matricula = new Matricula;
				$matricula->alumno_id		=	$alumno->id;
				$matricula->grupo_id		=	$grupo_id;
				$matricula->estado			=	"MATR";
				$matricula->created_by 		= 	$this->user->user_id;
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

		$imagen = ImageModel::find($alumno->foto_id);
		if ($imagen) {
			$alumno->foto_nombre = $imagen->nombre;
		}else{
			if ($alumno->sexo=='F') {
				$alumno->foto_nombre = 'default_female.png';
			}else{
				$alumno->foto_nombre = 'default_male.png';
			}
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
				$usuario->updated_by 	= $this->user->user_id;

				if (Request::has('password')) {
					if (Request::input('password') == ""){
						$usuario->password	=	Hash::make(Request::input('password'));
					}
				}

				$usuario->save();

				$alumno->user_id 	= $usuario->id;
				$alumno->updated_by = $this->user->user_id;
				
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
				$usuario->created_by 	= $this->user->user_id;
				$usuario->save();

				$alumno->user_id = $usuario->id;
				
				$alumno->save();

				$alumno->user = $usuario;
			}



			if (Request::input('grupo')['id']) {
				
				$grupo_id = Request::input('grupo')['id'];

				$matricula = Matricula::matricularUno($alumno->id, $grupo_id, false, $this->user->user_id);

				$grupo = Grupo::find($matricula->grupo_id);
				$alumno->grupo = $grupo;
			}


			return $alumno;
		} catch (Exception $e) {
			return abort('400', $e);
		}
	}



	/*************************************************************
	 * Guardar por VALOR
	 *************************************************************/
	public function putGuardarValor()
	{
		$alumno = Alumno::findOrFail(Request::input('alumno_id'));

		$guardarAlumno = new GuardarAlumno();
		return $guardarAlumno->valor($this->user, $alumno, Request::input('propiedad'), Request::input('valor'), $this->user->user_id);
		
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