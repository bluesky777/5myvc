<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;

class Grupo extends Model {
	use SoftDeletes;

	protected $fillable = [];
	protected $table = 'grupos';
	
	protected $dates = ['deleted_at', 'created_at'];
	protected $softDelete = true;


	public static function alumnos($grupo_id, $con_retirados='')
	{
		$consulta = '';

		if ($con_retirados=='') {
			// Consulta con solo los matriculados
			$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
							m.grupo_id, m.estado, 
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre
						FROM alumnos a 
						inner join matriculas m on a.id=m.alumno_id and m.grupo_id=? and m.estado="MATR" and m.deleted_at is null
						left join users u on a.user_id=u.id and u.deleted_at is null
						left join images i on i.id=u.imagen_id and i.deleted_at is null
						left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
						where a.deleted_at is null and m.deleted_at is null
						order by a.apellidos, a.nombres';
		}else{
			// Consulta con solo los matriculados y retirados.
			$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
							a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion,
							m.grupo_id, m.estado, 
							u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as imagen_nombre, 
							a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre,
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

	public static function detailed_materias($grupo_id)
	{
		$consulta = 'SELECT a.id as asignatura_id, a.grupo_id, a.profesor_id, a.creditos, a.orden,
				m.materia, m.alias as alias_materia, 
				p.id as profesor_id, p.nombres as nombres_profesor, p.apellidos as apellidos_profesor,
				p.foto_id, IFNULL(i.nombre, IF(p.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre
			FROM asignaturas a 
			inner join materias m on m.id=a.materia_id and m.deleted_at is null
			inner join profesores p on p.id=a.profesor_id and p.deleted_at is null
			left join images i on p.foto_id=i.id and i.deleted_at is null
			where a.grupo_id=:grupo_id and a.deleted_at is null
			order by a.orden, m.orden';

		$asignaturas = DB::select(DB::raw($consulta), array(':grupo_id' => $grupo_id));

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
						p.foto_id, IFNULL(i.nombre, IF(p.sexo="F","default_female.jpg", "default_male.jpg")) as foto_nombre,
						p.firma_id, i2.nombre as firma_titular_nombre
					FROM grupos g 
					left join profesores p on p.id=g.titular_id and p.deleted_at is null
					left join images i on p.foto_id=i.id and i.deleted_at is null
					left join images i2 on p.firma_id=i2.id and i.deleted_at is null
					where g.id=:grupo_id and g.deleted_at is null';

		$datos = DB::select(DB::raw($consulta), array(':grupo_id' => $grupo_id))[0];

		return $datos;
	}
}


