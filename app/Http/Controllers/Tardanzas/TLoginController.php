<?php namespace App\Http\Controllers\Tardanzas;


use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;

use Request;
use Auth;
use Hash;
use DB;


use App\Models\User;


class TLoginController extends Controller {


	public function postIndex()
	{

		$userTemp 	= [];
		$usuario 	= [];



		$credentials = [
			'username' => Request::input('username'),
			'password' => (string)Request::input('password')
		];
		
		if (Auth::attempt($credentials)) {
			$userTemp = Auth::user();

		}else if (Request::has('username') && Request::input('username') != ''){

			$pass = Hash::make((string)Request::input('password'));
			$usuario = User::where('password', '=', $pass)
							->where('username', '=', Request::input('username'))
							->get();

			if ( count( $usuario) > 0) {
				$userTemp = Auth::login($usuario[0]);
			}else{
				$usuario = User::where('password', '=', (string)Request::input('password'))
							->where('username', '=', Request::input('username'))
							->get();
				if ( count( $usuario) > 0) {
					$usuario[0]->password = Hash::make((string)$usuario[0]->password);
					$usuario[0]->save();
					$userTemp = Auth::loginUsingId($usuario[0]->id);
				}else{
					return abort(400, 'Credenciales inválidas.');
				}
			}
		}else{
			return abort(401, 'Por favor ingrese de nuevo.');
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
		$usuario = array_merge($usuario, $userTemp);
		$usuario = (object)$usuario;
				

		$cons_alum = "SELECT a.id, a.nombres, a.apellidos, sexo, user_id, a.fecha_nac, a.religion, a.pazysalvo, a.deuda,
						m.id as matricula_id, m.grupo_id, m.estado, g.nombre as nombre_grupo, g.abrev, g.year_id
					 from alumnos a
					inner join matriculas m on m.alumno_id=a.id
					inner join grupos g on g.id=m.grupo_id
					inner join years y on y.id=g.year_id and year_id=:year_id";
		
		$alumnos = DB::select($cons_alum, [':year_id' => $usuario->year_id]);
		$usuario->alumnos = $alumnos;

		//return json_decode(json_encode($user[0]), true);

		return json_decode(json_encode($usuario), true);
	}

	function default_image_id($sexo)
	{
		if ($sexo == 'F') {
			return 2;
		}else{
			return 1; // ID de la imagen masculina
		}
	}
	function default_image_name($sexo)
	{
		if ($sexo == 'F') {
			return 'default_female.jpg';
		}else{
			return 'default_male.jpg';
		}
	}



}