<?php namespace App\Models;


use Tymon\JWTAuth\Contracts\JWTSubject as AuthenticatableUserContract;


use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


use Illuminate\Database\Eloquent\SoftDeletes;

use Zizaco\Entrust\Traits\EntrustUserTrait;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;



use Request;
use DB;



class User extends Authenticatable implements AuthenticatableUserContract
{
    use Notifiable;


	use SoftDeletes, EntrustUserTrait {

        SoftDeletes::restore insteadof EntrustUserTrait;
        EntrustUserTrait::restore insteadof SoftDeletes;

    }
    
	protected $softDelete = true;

	protected $dates = ['deleted_at', 'created_at'];

	protected $table = 'users';


	protected $fillable = [
        'name', 'email', 'password',
    ];

	protected $hidden = [
        'password', 'remember_token',
    ];


	public static $nota_minima_aceptada = 0;
	public static $images = '';
	public static $perfilPath = '';
	public static $imgSharedPath = '';


	public static function fromToken($already_parsed=false, $request = false)
	{
		$userTemp = [];
		$usuario = [];
		$token = [];



		try
		{
			if ($already_parsed) {

				$token = $already_parsed;
				$userTemp = JWTAuth::toUser($token);

			}else{

				try{
					$token = JWTAuth::parseToken();
				}catch(JWTException $e){
					// No haremos nada, continuaremos verificando datos.
				}	

				try {
					if ($token){
						// Lleva aquí y ocurre un error cuando se ha demorado mucho en mover la página.
						$userTemp = $token->authenticate();
					}else {
						return response()->json(['error' => 'No existe Token'], 401);
					}
				} catch (JWTException $e) {
					/*
					$tok = JWTAuth::getToken();
					$tok->get(); // Sí hay token, definitivamente está expirado :(
					*/
					abort(401, 'Token ha expirado.');
				}
				

				

			}


			if (!$userTemp) {
				abort(401, 'Token inválido, prohibido entrar.');
			}

			if (!$userTemp->periodo_id) {
				$userTemp->periodo_id = Periodo::where('actual', '=', true)->first()->id;
				$userTemp->save();
			}

			$consulta = '';
			$tipo_tmp = $userTemp->tipo;
			$is_super = $userTemp->is_superuser;

			switch ($tipo_tmp) {  // Alumno, Profesor, Acudiente, Usuario.
				case 'Profesor':
					
					$consulta = 'SELECT p.id as persona_id, p.nombres, p.apellidos, p.sexo, p.fecha_nac, p.ciudad_nac, p.user_id, u.username, 
									IFNULL(i.nombre, IF(p.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
									p.foto_id, IFNULL(i2.nombre, IF(p.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
									p.firma_id, i3.nombre as firma_nombre, 
									"N/A" as grupo_id, ("N/A") as nombre_grupo, ("N/A") as abrev_grupo, 
									"N/A" as year_matricula_id, per.id as periodo_id, per.numero as numero_periodo, per.profes_pueden_editar_notas, per.profes_pueden_nivelar,
									y.id as year_id, y.year, y.nota_minima_aceptada, y.actual as year_actual, per.actual as periodo_actual, 
									y.unidad_displayname, y.subunidad_displayname, y.unidades_displayname, y.subunidades_displayname, 
									y.genero_unidad, y.genero_subunidad, per.fecha_plazo, y.alumnos_can_see_notas, y.logo_id,
									y.si_recupera_materia_recup_indicador, y.year_pasado_en_bol, y.mostrar_puesto_boletin, y.mostrar_nota_comport_boletin, y.profes_can_edit_alumnos
								from profesores p 
								left join images i on i.id=:imagen_id
								left join images i2 on i2.id=p.foto_id
								left join images i3 on i3.id=p.firma_id
								left join periodos per on per.id=:periodo_id
								left join years y on y.id=per.year_id
								left join users u on u.id=p.user_id
								where p.deleted_at is null and p.user_id=:user_id';

					$usuario = DB::select($consulta, array(
						':user_id'		=> $userTemp->id, 
						':imagen_id'	=> $userTemp->imagen_id, 
						':periodo_id'	=> $userTemp->periodo_id,
					));
					
					break;


				case 'Alumno':
					
					$consulta = 'SELECT a.id as persona_id, a.nombres, a.apellidos, a.user_id, 
									a.sexo, a.fecha_nac, a.ciudad_nac, a.pazysalvo, a.deuda, u.username,
									IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
									a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
									g.id as grupo_id, g.nombre as nombre_grupo, g.abrev as abrev_grupo, 
									g.year_id as year_matricula_id, per.id as periodo_id, per.numero as numero_periodo, 
									y.id as year_id, y.year, y.nota_minima_aceptada, y.actual as year_actual, per.actual as periodo_actual, 
									y.unidad_displayname, y.subunidad_displayname, y.unidades_displayname, y.subunidades_displayname, 
									y.genero_unidad, y.genero_subunidad, per.fecha_plazo, y.mostrar_nota_comport_boletin, y.si_recupera_materia_recup_indicador, y.year_pasado_en_bol, y.alumnos_can_see_notas, y.logo_id
								from alumnos a 
								inner join matriculas m on m.alumno_id=a.id and (m.estado="MATR" or m.estado="ASIS")
								inner join grupos g on g.id=m.grupo_id
								left join images i on i.id=:imagen_id
								left join images i2 on i2.id=a.foto_id
								left join periodos per on per.id=:periodo_id
								inner join years y on y.id=per.year_id and g.year_id=y.id 
								left join users u on u.id=a.user_id
								where a.deleted_at is null and a.user_id=:user_id';
					
					$usuario = DB::select($consulta, array(
						':user_id'		=> $userTemp->id, 
						':imagen_id'	=> $userTemp->imagen_id, 
						':periodo_id'	=> $userTemp->periodo_id,
					));

					break;


				case 'Acudiente':
					
					$consulta = 'SELECT ac.id as persona_id, ac.nombres, ac.apellidos, ac.user_id, u.username, u.is_superuser,
									ac.sexo, u.email, ac.fecha_nac, ac.ciudad_nac, 
									u.imagen_id, IFNULL(i.nombre, IF(ac.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
									ac.foto_id, IFNULL(i2.nombre, IF(ac.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
									"N/A" as grupo_id, ("N/A") as nombre_grupo, ("N/A") as abrev_grupo, 
									"N/A" as year_matricula_id, per.id as periodo_id, per.numero as numero_periodo, 
									y.id as year_id, y.year, y.nota_minima_aceptada, y.actual as year_actual, per.actual as periodo_actual, 
									y.unidad_displayname, y.subunidad_displayname, y.unidades_displayname, y.subunidades_displayname, 
									y.genero_unidad, y.genero_subunidad, per.fecha_plazo, y.si_recupera_materia_recup_indicador, y.mostrar_nota_comport_boletin, y.alumnos_can_see_notas, y.logo_id
								from acudientes ac 
								left join images i on i.id=:imagen_id
								left join images i2 on i2.id=ac.foto_id
								left join periodos per on per.id=:periodo_id
								inner join years y on y.id=per.year_id  
								left join users u on u.id=ac.user_id
								where ac.deleted_at is null and ac.user_id=:user_id';

					$usuario = DB::select($consulta, array(
						':user_id'		=> $userTemp->id, 
						':imagen_id'	=> $userTemp->imagen_id, 
						':periodo_id'	=> $userTemp->periodo_id,
					));

					break;
				

				case 'Usuario':
					
					$consulta = 'SELECT u.id as persona_id, "" as nombres, "" as apellidos, u.id as user_id, u.username, u.is_superuser, u.tipo, 
									u.sexo, u.email, "N/A" as fecha_nac, "N/A" as ciudad_nac, 
									u.imagen_id, IFNULL(i.nombre, IF(u.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
									u.imagen_id as foto_id, IFNULL(i.nombre, IF(u.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
									"N/A" as grupo_id, ("N/A") as nombre_grupo, ("N/A") as abrev_grupo, 
									"N/A" as year_matricula_id, per.id as periodo_id, per.numero as numero_periodo, per.profes_pueden_editar_notas, per.profes_pueden_nivelar,
									y.id as year_id, y.year, y.nota_minima_aceptada, y.actual as year_actual, per.actual as periodo_actual, 
									y.unidad_displayname, y.subunidad_displayname, y.unidades_displayname, y.subunidades_displayname, 
									y.genero_unidad, y.genero_subunidad, per.fecha_plazo, y.si_recupera_materia_recup_indicador, y.year_pasado_en_bol, y.mostrar_nota_comport_boletin, y.alumnos_can_see_notas, y.logo_id
								from users u
								left join periodos per on per.id=u.periodo_id
								left join years y on y.id=per.year_id
								left join images i on i.id=u.imagen_id 
								where u.id=:user_id and u.deleted_at is null';

					$usuario = DB::select($consulta, array(
						':user_id'		=> $userTemp->id
					));

					break;
				
			}


			
			$usuario = (array)$usuario[0];
			$userTemp = (array)$userTemp['attributes'];
			//return $userTemp;

			$usuario = array_merge($usuario, $userTemp);
			$usuario = (object)$usuario;			

			if (! isset( $usuario->tipo) ) {
				$usuario->tipo = $tipo_tmp;
			}
			if (! isset( $usuario->is_superuser) ) {
				$usuario->is_superuser = $is_super;
			}


			User::$nota_minima_aceptada = $usuario->nota_minima_aceptada;
			User::$images 				= 'images/';
			User::$perfilPath 			= User::$images . 'perfil/';
			User::$imgSharedPath 		= User::$images . 'shared/';

		}
		catch(Tymon\JWTAuth\Exceptions\TokenExpiredException $e)
		{
			if (! count(Request::all())) {
				return Response::json(['error' => 'token_expired'], 401);
			}
		}
		/*
		catch(JWTException $e){
			// No haremos nada, continuaremos verificando datos.
			return response()->json(['error' => $e], 401);
		}
		*/


		// *************************************************
		//    Traeremos los roles y permisos
		// *************************************************
		$user = User::find($usuario->user_id);
		$usuario->roles = $user->roles()->get();
		$perms = [];

		foreach($usuario->roles as $role )
		{
			$consulta = 'SELECT pm.name, pm.display_name, pm.description from permission_role pmr
					inner join permissions pm on pm.id = pmr.permission_id 
						and pmr.role_id = :role_id';
			
			$permisos = DB::select($consulta, array(':role_id' => $role->id));
			
			foreach ($permisos as $permiso) {
				array_push($perms, $permiso->name);
			}
		}

		$usuario->perms = $perms;
		$usuario->token = $token;

		return $usuario;
	}

	// Todos los permisos de un usuario, con el objeto permiso, o solo con el string name del permiso
	public function permissions($detailed=false)
	{
		$perms = [];

		foreach( $this->roles()->get() as $role )
		{
			$permisos = $role->permissions($detailed);
			// No quiero un array con multiples arrays dentro que contengan los permisos
			// así que recorro cada array con permisos y voy agregando cada elemento permiso al array $perms donde estarán unidos.
			foreach ($permisos as $value) {
				array_push($perms, $value);
			}
		}

		return $perms;
	}




	/**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();  // Eloquent model method
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
             'user' => [ 
                'id' => $this->id,
             ]
        ];
	}
	
	
	public static function pueden_editar_notas($user)
	{
		$periodos = DB::select('SELECT * FROM periodos p WHERE p.deleted_at is null and p.year_id=?', [$user->year_id]);
		
		$num_periodo = (int)Request::input('num_periodo');
		
		if ($num_periodo) {
			# Todo bien
		}else{
			$num_periodo = (int)$user->numero_periodo;
		}
		
		$cant_p = count($periodos);
		
		for ($i=0; $i < $cant_p; $i++) { 
			if ($periodos[$i]->numero == $num_periodo){
				$user->profes_pueden_nivelar 		= $periodos[$i]->profes_pueden_nivelar;
				$user->profes_pueden_editar_notas 	= $periodos[$i]->profes_pueden_editar_notas;
			}
		}
		
		if ($user->roles[0]->name == 'Profesor' && $user->profes_pueden_editar_notas==0) {
			return abort(400, 'No tienes permiso');
		}else if(($user->roles[0]->name == 'Admin' && $user->is_superuser) || $user->roles[0]->name == 'Profesor'){
			// todo bien
		}else{
			return App::abort(400, 'No tienes permiso.');
		}

	}

	
	public static function pueden_modificar_definitivas($user)
	{
		$periodos = DB::select('SELECT * FROM periodos p WHERE p.deleted_at is null and p.year_id=?', [$user->year_id]);
		
		$num_periodo = (int)Request::input('num_periodo');
		
		if ($num_periodo) {
			# Todo bien
		}else{
			$num_periodo = (int)$user->numero_periodo;
		}
		
		$cant_p = count($periodos);
		
		for ($i=0; $i < $cant_p; $i++) { 
			if ($periodos[$i]->numero == $num_periodo){
				$user->profes_pueden_nivelar 		= $periodos[$i]->profes_pueden_nivelar;
				$user->profes_pueden_editar_notas 	= $periodos[$i]->profes_pueden_editar_notas;
			}
		}
		
		
		if ($user->roles[0]->name == 'Profesor' && $user->profes_pueden_nivelar==0) {
			return abort(400, 'No tienes permiso');
		}else if($user->roles[0]->name == 'Admin' && $user->is_superuser){
			// todo bien
		}else if($user->roles[0]->name == 'Profesor' && $user->profes_pueden_nivelar==1){
			// todo bien
		}else{
			return abort(400, 'No tienes permiso.');
		}

	}

}


