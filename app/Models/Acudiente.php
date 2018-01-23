<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Acudiente extends Model {
	use SoftDeletes;
	protected $fillable = [];
	
	protected $dates = ['deleted_at', 'created_at'];
	protected $softDelete = true;


	public static $consulta_acudientes_de_alumno = 'SELECT ac.id, ac.nombres, ac.apellidos, ac.sexo, ac.fecha_nac, ac.ciudad_nac, c1.ciudad as ciudad_nac_nombre, ac.ciudad_doc, c2.ciudad as ciudad_doc_nombre, ac.telefono, pa.parentesco, pa.id as parentesco_id, ac.user_id, 
							ac.celular, ac.ocupacion, ac.email, ac.barrio, ac.direccion, ac.tipo_doc, ac.documento, ac.created_by, ac.updated_by, ac.created_at, ac.updated_at, 
							ac.foto_id, IFNULL(i.nombre, IF(ac.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
							u.username, u.is_active
						FROM parentescos pa
						left join acudientes ac on ac.id=pa.acudiente_id and ac.deleted_at is null
						left join users u on ac.user_id=u.id and u.deleted_at is null
						left join images i on i.id=ac.foto_id and i.deleted_at is null
						left join ciudades c1 on c1.id=ac.ciudad_nac and c1.deleted_at is null
						left join ciudades c2 on c2.id=ac.ciudad_doc and c2.deleted_at is null
						WHERE pa.alumno_id=? and pa.deleted_at is null';


}