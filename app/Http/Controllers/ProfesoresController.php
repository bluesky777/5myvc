<?php namespace App\Http\Controllers;


use DB;
use Request;

use App\Models\User;
use App\Models\Profesor;
use App\Models\Role;
use App\Models\Year;
use Hash;


class ProfesoresController extends Controller {


	public function getIndex()
	{
		$user = User::fromToken();

		$consulta = 'SELECT p.id, p.nombres, p.apellidos, p.sexo, p.foto_id, p.tipo_doc,
					p.num_doc, p.ciudad_doc, p.fecha_nac, p.ciudad_nac, p.titulo,
					p.estado_civil, p.barrio, p.direccion, p.telefono, p.celular,
					p.facebook, p.email, p.tipo_profesor, p.user_id, u.username,
					u.email as email_usu, u.imagen_id, u.is_superuser,
					c.id as contrato_id, c.year_id,
					p.foto_id, IFNULL(i.nombre, IF(p.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre
				from profesores p
				left join users u on p.user_id=u.id and u.is_Active=false
				left join contratos c on c.profesor_id=p.id and c.year_id=:year_id and c.deleted_at is null
				LEFT JOIN images i on i.id=p.foto_id and i.deleted_at is null
				where p.deleted_at is null
				order by p.nombres, p.apellidos';

		$profesores = DB::select($consulta, array(':year_id'=>$user->year_id));
		return $profesores;
	}


	public function postStore()
	{


		$this->sanarInputProfesor();

		$profesor = new Profesor;
		$profesor->nombres		=	Request::input('nombres');
		$profesor->apellidos	=	Request::input('apellidos');
		$profesor->sexo			=	Request::input('sexo');
		$profesor->tipo_doc		=	Request::input('tipo_doc');
		$profesor->num_doc		=	Request::input('num_doc');
		$profesor->ciudad_doc	=	Request::input('ciudad_doc');
		$profesor->fecha_nac	=	Request::input('fecha_nac');
		$profesor->ciudad_nac	=	Request::input('ciudad_nac');
		$profesor->titulo		=	Request::input('titulo');
		$profesor->estado_civil	=	Request::input('estado_civil');
		$profesor->barrio		=	Request::input('barrio');
		$profesor->direccion	=	Request::input('direccion');
		$profesor->telefono		=	Request::input('telefono');
		$profesor->celular		=	Request::input('celular');
		$profesor->facebook		=	Request::input('facebook');
		$profesor->email		=	Request::input('email');
		$profesor->tipo_profesor	=	Request::input('tipo_profesor');
		$profesor->save();
		

		$this->sanarInputUser();

		$this->checkOrChangeUsername($profesor->user_id);

		$usuario = new User;
		$usuario->username		=	Request::input('username');
		$usuario->password		=	Hash::make(Request::input('password', '123456'));
		$usuario->email			=	Request::input('email2');
		$usuario->is_superuser	=	Request::input('is_superuser', false);
		$usuario->is_active		=	Request::input('is_active', true);
		$usuario->tipo			=	'Profesor';
		$usuario->save();


		$profesor->user_id = $usuario->id;
		
		$role = Role::where('name', '=', 'Profesor')->get();
		$usuario->attachRole($role[0]);

		$profesor->save();

		$profesor->user = $usuario;

		if (Request::input('grupo')['id']) {
			$grupo_id = Request::input('grupo')['id'];

			$matricula = new Matricula;
			$matricula->alumno_id	=	$profesor->id;
			$matricula->grupo_id	=	$grupo_id;
			$matricula->matriculado	=	true;
			$matricula->save();

			$grupo = Grupo::find($matricula->grupo_id);
			$profesor->grupo = $grupo;
		}


		return $profesor;
		
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

		if (!Request::input('is_superuser')) {

			Request::merge(array('is_superuser' => false));
			
		}
	}


	public function sanarInputProfesor(){
		if (is_array( Request::input('tipo_sangre') )){
			if (!array_key_exists('sangre', Request::input('tipo_sangre'))) {
				Request::merge(array('tipo_sangre' => array('sangre'=>'')));
			}
		}else{
			Request::merge(array('tipo_sangre' => array('sangre'=>'')));
		}

		if (Request::input('estado_civil')) {
			if (isset(Request::input('estado_civil')['estado_civil'])) {
				Request::merge(array('estado_civil' => Request::input('estado_civil')['estado_civil'] ) );
			}
		}else{
			Request::merge(array('estado_civil' => null) );
		}


		if (Request::input('ciudad_nac')['id']) {
			Request::merge(array('ciudad_nac' => Request::input('ciudad_nac')['id'] ) );
		}else{
			Request::merge(array('ciudad_nac' => null) );
		}

		if (Request::input('tipo_doc')['id']) {
			Request::merge(array('tipo_doc' => Request::input('tipo_doc')['id'] ) );
		}else{
			Request::merge(array('tipo_doc' => null) );
		}

		if (Request::input('foto')['id']) {
			Request::merge(array('foto_id' => Request::input('foto')['id'] ) );
		}else{
			Request::merge(array('foto_id' => null) );
		}
	}



	public function getShow($id)
	{
		$profesor = Profesor::detallado($id);
		return array( $profesor );
	}



	public function putUpdate($id)
	{
		
		$this->sanarInputUser();
		$this->sanarInputProfesor();


		$profesor = Profesor::findOrFail($id);
		try {
			$profesor->nombres		=	Request::input('nombres_profesor');
			$profesor->apellidos	=	Request::input('apellidos_profesor');
			$profesor->sexo			=	Request::input('sexo');
			$profesor->tipo_doc		=	Request::input('tipo_doc');
			$profesor->num_doc		=	Request::input('num_doc');
			$profesor->ciudad_doc	=	Request::input('ciudad_doc');
			$profesor->fecha_nac	=	Request::input('fecha_nac');
			$profesor->ciudad_nac	=	Request::input('ciudad_nac');
			$profesor->titulo		=	Request::input('titulo');
			$profesor->estado_civil	=	Request::input('estado_civil');
			$profesor->barrio		=	Request::input('barrio');
			$profesor->direccion	=	Request::input('direccion');
			$profesor->telefono		=	Request::input('telefono');
			$profesor->celular		=	Request::input('celular');
			$profesor->facebook		=	Request::input('facebook');
			$profesor->email		=	Request::input('email');
			//$profesor->tipo_profesor	=>	Request::input('tipo_profesor')


			$profesor->save();

			if ($profesor->user_id and Request::input('username')) {
				
				$this->sanarInputUser();
				
				$this->checkOrChangeUsername($profesor->user_id);

				$usuario = User::find($profesor->user_id);
				$usuario->username		=	Request::input('username');
				$usuario->email			=	Request::input('email2');
				$usuario->is_superuser	=	Request::input('is_superuser', false);
				$usuario->is_active		=	Request::input('is_active', true);

				if (Request::input('password')){
					if (Request::input('password') != "") {
						$usuario->password = Hash::make(Request::input('password'));
					}
				}

				$usuario->save();

				$profesor->user_id = $usuario->id;
				
				$profesor->save();

				$profesor->user = $usuario;
			} else if (!$profesor->user_id and Request::input('username')) {
				
				$this->sanarInputUser();
				$this->checkOrChangeUsername($profesor->user_id);

				$usuario = new User;
				$usuario->username		=	Request::input('username');
				$usuario->password		=	Hash::make(Request::input('password', '123456'));
				$usuario->email			=	Request::input('email2');
				$usuario->is_superuser	=	Request::input('is_superuser', false);
				$usuario->is_active		=	Request::input('is_active', true);
				$usuario->save();


				$profesor->user_id = $usuario->id;
				
				$profesor->save();

				$profesor->user = $usuario;
			}

			return $profesor;
		} catch (Exception $e) {
			return abort(400, $e);
		}
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



	public function getConyears(){

		$user = User::fromToken();

		$profesores = Profesor::fromyear($user->year_id);

		foreach ($profesores as $profesor) {
			$profesor->years = Year::de_un_profesor($profesor->id);
		}
		
		return $profesores;
		
	}


	public function deleteDestroy($id)
	{
		$user = User::fromToken();
		$profesor = Profesor::find($id);
		//$queries = DB::getQueryLog();
		//$last_query = end($queries);
		//return $last_query;

		if ($profesor) {
			$profesor->delete();
		}else{
			return abort(400, 'Profesor no existe o está en Papelera.');
		}
		return $profesor;
	
	}	

	public function deleteForcedelete($id)
	{
		$user = User::fromToken();
		$profesor = Profesor::onlyTrashed()->findOrFail($id);
		
		if ($profesor) {
			$profesor->forceDelete();
		}else{
			return abort(400, 'Profesor no encontrado en la Papelera.');
		}
		return $profesor;
	
	}

	public function putRestore($id)
	{
		$user = User::fromToken();
		$profesor = Profesor::onlyTrashed()->findOrFail($id);

		if ($profesor) {
			$profesor->restore();
		}else{
			return abort(400, 'Profesor no encontrado en la Papelera.');
		}
		return $profesor;
	}


	public function getTrashed()
	{
		$user = User::fromToken();
		$consulta = 'SELECT m2.matricula_id, a.id as alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
				a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
				m2.year_id, m2.grupo_id, m2.nombregrupo, m2.abrevgrupo, IFNULL(m2.actual, -1) as currentyear,
				u.username, u.is_superuser, u.is_active
			FROM alumnos a left join 
				(select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 0 as actual
				from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=1
				and m.alumno_id NOT IN 
					(select m.alumno_id
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=2)
					union
					select m.id as matricula_id, g.year_id, m.grupo_id, m.alumno_id, g.nombre as nombregrupo, g.abrev as abrevgrupo, 1 AS actual
					from matriculas m INNER JOIN grupos g ON m.grupo_id=g.id and g.year_id=2
				)m2 on a.id=m2.alumno_id
			left join users u on u.id=a.user_id where a.deleted_at is not null
			order by p.nombres, p.apellidos';

		return DB::select(DB::raw($consulta));
	}

}