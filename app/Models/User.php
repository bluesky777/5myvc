<?php namespace App\Models;


use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Illuminate\Database\Eloquent\SoftDeletes;

use Zizaco\Entrust\Traits\EntrustUserTrait;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;



use Request;
use DB;



class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	use EntrustUserTrait;

	use SoftDeletes;
	protected $softDelete = true;

	protected $dates = ['deleted_at', 'created_at'];

	protected $table = 'users';

	protected $hidden = array('password', 'remember_token');


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

			switch ($userTemp->tipo) {  // Alumno, Profesor, Acudiente, Usuario.
				case 'Profesor':
					
					$consulta = 'SELECT p.id as persona_id, p.nombres, p.apellidos, p.sexo, p.fecha_nac, p.ciudad_nac, p.user_id, 
									IFNULL(i.nombre, IF(p.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
									p.foto_id, IFNULL(i2.nombre, IF(p.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre, 
									"N/A" as grupo_id, ("N/A") as nombre_grupo, ("N/A") as abrev_grupo, 
									"N/A" as year_matricula_id, per.id as periodo_id, per.numero as numero_periodo, 
									y.id as year_id, y.year, y.nota_minima_aceptada, y.actual as year_actual, per.actual as periodo_actual, 
									y.unidad_displayname, y.subunidad_displayname, y.unidades_displayname, y.subunidades_displayname, 
									y.genero_unidad, y.genero_subunidad, per.fecha_plazo, y.alumnos_can_see_notas
								from profesores p 
								left join images i on i.id=:imagen_id
								left join images i2 on i2.id=p.foto_id
								left join periodos per on per.id=:periodo_id
								left join years y on y.id=per.year_id
								where p.deleted_at is null and p.user_id=:user_id';

					$usuario = DB::select($consulta, array(
						':user_id'		=> $userTemp->id, 
						':imagen_id'	=> $userTemp->imagen_id, 
						':periodo_id'	=> $userTemp->periodo_id,
					));
					
					break;


				case 'Alumno':
					
					$consulta = 'SELECT a.id as persona_id, a.nombres, a.apellidos, a.user_id, 
									a.sexo, a.fecha_nac, a.ciudad_nac, a.pazysalvo, a.deuda,
									IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
									a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre, 
									g.id as grupo_id, g.nombre as nombre_grupo, g.abrev as abrev_grupo, 
									g.year_id as year_matricula_id, per.id as periodo_id, per.numero as numero_periodo, 
									y.id as year_id, y.year, y.nota_minima_aceptada, y.actual as year_actual, per.actual as periodo_actual, 
									y.unidad_displayname, y.subunidad_displayname, y.unidades_displayname, y.subunidades_displayname, 
									y.genero_unidad, y.genero_subunidad, per.fecha_plazo, y.alumnos_can_see_notas
								from alumnos a 
								inner join matriculas m on m.alumno_id=a.id and m.matriculado=true
								inner join grupos g on g.id=m.grupo_id
								left join images i on i.id=:imagen_id
								left join images i2 on i2.id=a.foto_id
								left join periodos per on per.id=:periodo_id
								left join years y on y.id=per.year_id
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
									u.imagen_id, IFNULL(i.nombre, IF(ac.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
									ac.foto_id, IFNULL(i2.nombre, IF(ac.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre, 
									"N/A" as grupo_id, ("N/A") as nombre_grupo, ("N/A") as abrev_grupo, 
									"N/A" as year_matricula_id, per.id as periodo_id, per.numero as numero_periodo, 
									y.id as year_id, y.year, y.nota_minima_aceptada, y.actual as year_actual, per.actual as periodo_actual, 
									y.unidad_displayname, y.subunidad_displayname, y.unidades_displayname, y.subunidades_displayname, 
									y.genero_unidad, y.genero_subunidad, per.fecha_plazo, y.alumnos_can_see_notas
								from acudientes ac 
								left join images i on i.id=:imagen_id
								left join images i2 on i2.id=ac.foto_id
								left join periodos per on per.id=:periodo_id
								left join years y on y.id=per.year_id
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
									u.imagen_id, IFNULL(i.nombre, IF(u.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
									u.imagen_id as foto_id, IFNULL(i.nombre, IF(u.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre, 
									"N/A" as grupo_id, ("N/A") as nombre_grupo, ("N/A") as abrev_grupo, 
									"N/A" as year_matricula_id, per.id as periodo_id, per.numero as numero_periodo, 
									y.id as year_id, y.year, y.nota_minima_aceptada, y.actual as year_actual, per.actual as periodo_actual, 
									y.unidad_displayname, y.subunidad_displayname, y.unidades_displayname, y.subunidades_displayname, 
									y.genero_unidad, y.genero_subunidad, per.fecha_plazo, y.alumnos_can_see_notas
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

}


