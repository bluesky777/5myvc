<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use App\Models\Debugging;

class Grupo extends Model {
	use SoftDeletes;

	protected $fillable = [];
	protected $table = 'grupos';
	
	protected $dates = ['deleted_at', 'created_at'];
	protected $softDelete = true;
	
	
	public static $consulta_grupos_titularia = 'SELECT g.id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
							p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
							g.created_at, g.updated_at, gra.nombre as nombre_grado 
						from grupos g
						inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
						inner join profesores p on p.id=g.titular_id and p.id=:titular_id
						where g.deleted_at is null
						order by g.orden';



	public static function alumnos($grupo_id, $con_retirados='')
	{
		$consulta = '';

		if ($con_retirados=='') {
			// Consulta con solo los matriculados
			$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
							m.grupo_id, m.estado, 
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id and m.grupo_id=? and (m.estado="MATR" or m.estado="ASIS") and m.deleted_at is null
						left join users u on a.user_id=u.id and u.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where a.deleted_at is null and m.deleted_at is null
						order by a.apellidos, a.nombres';
		}else{
			// Consulta incluyendo los matriculados y retirados.
			$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
							m.grupo_id, m.estado, 
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre,
							m.fecha_retiro as fecha_retiro 
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id and m.grupo_id=?  
						left join users u on a.user_id=u.id and u.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where a.deleted_at is null and m.deleted_at is null
						order by a.apellidos, a.nombres';
		}

		$alumnos = DB::select($consulta, array($grupo_id));

		return $alumnos;
		//return $this->belongsToMany('Alumno', 'matriculas');
	}

	public static function detailed_materias($grupo_id, $profesor_id=null, $exceptuando=false)
	{
		$complemento = ''; // Para complementar la consulta
		if ($profesor_id) {
			if ($exceptuando) {
				$complemento = ' and p.id!='.$profesor_id. ' ';
			}else{
				$complemento = ' and p.id='.$profesor_id. ' ';
			}
		}

		$consulta = 'SELECT a.id as asignatura_id, a.grupo_id, a.profesor_id, a.creditos, a.orden,
				m.materia, m.alias as alias_materia, 
				p.id as profesor_id, p.nombres as nombres_profesor, p.apellidos as apellidos_profesor,
				p.foto_id, IFNULL(i.nombre, IF(p.sexo="F","default_female.png", "default_male.png")) as foto_nombre
			FROM asignaturas a 
			inner join materias m on m.id=a.materia_id and m.deleted_at is null
			inner join profesores p on p.id=a.profesor_id and p.deleted_at is null' . $complemento .
			' left join images i on p.foto_id=i.id and i.deleted_at is null
			where a.grupo_id=:grupo_id and a.deleted_at is null
			order by a.orden, m.orden';

		$asignaturas = DB::select($consulta, [':grupo_id' => $grupo_id]);

		return $asignaturas;
	}

	public function materias()
	{
		return $this->belongsToMany('Materia', 'asignaturas');
	}

	public function asignaturas()
	{
		return $this->hasMany('Asignatura');
	}

	public static function datos($grupo_id)
	{
		$consulta = 'SELECT g.id as grupo_id, g.titular_id, g.nombre as nombre_grupo, g.abrev as abrev_grupo,
						g.caritas,
						p.nombres as nombres_profesor, p.apellidos as apellidos_profesor,
						p.foto_id, IFNULL(i.nombre, IF(p.sexo="F","default_female.png", "default_male.png")) as foto_nombre,
						p.firma_id, i2.nombre as firma_titular_nombre
					FROM grupos g 
					left join profesores p on p.id=g.titular_id and p.deleted_at is null
					left join images i on p.foto_id=i.id and i.deleted_at is null
					left join images i2 on p.firma_id=i2.id and i.deleted_at is null
					where g.id=:grupo_id and g.deleted_at is null';

		$datos = DB::select($consulta, [':grupo_id' => $grupo_id])[0];

		return $datos;
	}
}


