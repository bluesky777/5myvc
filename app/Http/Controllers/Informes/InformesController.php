<?php namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\Year;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Asignatura;
use App\Models\Subunidad;
use App\Models\Profesor;


class InformesController extends Controller {

	public function putDatos()
	{
		$user 	= User::fromToken();
		$res 	= [];

		$year 	= Year::datos($user->year_id, true); // Datos del año actual
		
		$consulta = 'SELECT g.id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
						p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
						g.created_at, g.updated_at, gra.nombre as nombre_grado 
					from grupos g
					inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
					left join profesores p on p.id=g.titular_id
					where g.deleted_at is null
					order by g.orden';

		$grupos = DB::select($consulta, [':year_id'=>$user->year_id] );


		$consulta = 'SELECT p.id as profesor_id, p.nombres, p.apellidos, p.sexo, p.foto_id, p.tipo_doc,
						p.num_doc, p.ciudad_doc, p.fecha_nac, p.ciudad_nac, p.titulo,
						p.estado_civil, p.barrio, p.direccion, p.telefono, p.celular,
						p.facebook, p.email, p.tipo_profesor, p.user_id, u.username,
						u.email as email_usu, u.imagen_id, u.is_superuser,
						c.id as contrato_id, c.year_id,
						p.foto_id, IFNULL(i.nombre, IF(p.sexo="F","default_female.png", "default_male.png")) as foto_nombre
					from profesores p
					inner join contratos c on c.profesor_id=p.id and c.year_id=:year_id and c.deleted_at is null
					left join users u on p.user_id=u.id and u.deleted_at is null
					LEFT JOIN images i on i.id=p.foto_id and i.deleted_at is null
					where p.deleted_at is null
					order by p.nombres, p.apellidos';

		$profesores = DB::select($consulta, [':year_id'=>$user->year_id] );

		$consulta = 'SELECT * 
				from images i where i.deleted_at is null and i.publica=true';

		$imagenes = DB::select($consulta, [':user_id'=>$user->user_id] );
		
		$periodos_desactualizados = $this->grupos_desactualizados($user);
		if (count($periodos_desactualizados)>0) {
			$res['periodos_desactualizados'] 	= $periodos_desactualizados;
		}
		
		// Los periodos con grupos aunque no estén desactualizados
		$consulta 	= 'SELECT * FROM periodos WHERE deleted_at is null and year_id=?';	
		$periodos 	= DB::select($consulta, [$user->year_id]);
		
		$consulta 	= 'SELECT *, id as grupo_id FROM grupos WHERE deleted_at is null and year_id=?';
		$grupos 	= DB::select($consulta, [ $user->year_id ]);	
		
		$cant_pers = count($periodos);
		
		for ($i=0; $i < $cant_pers; $i++) { 
			$periodos[$i]->grupos = $grupos;		
		}
		
		

		$res['year'] 		= $year;
		$res['grupos'] 		= $grupos;
		$res['profesores'] 	= $profesores;
		$res['imagenes'] 	= $imagenes;
		$res['periodos_grupos'] 	= $periodos;
		

		return $res;
	}
	
	
	
	
	private function grupos_desactualizados(&$user){
		$consulta 	= 'SELECT * FROM periodos WHERE deleted_at is null and year_id=?';	
		$periodos 	= DB::select($consulta, [$user->year_id]);
		
		$cant_pers = count($periodos);
		
		for ($i=0; $i < $cant_pers; $i++) { 
			$consulta 	= 'SELECT n.updated_at as n_updated_at, nf.updated_at as nf_updated_at, n.grupo_id, n.nombre, n.abrev FROM
							(SELECT max(n.updated_at) as updated_at, g.id as grupo_id, g.nombre, g.abrev
							FROM notas n
							inner join subunidades s on s.id=n.subunidad_id and s.deleted_at is null and n.deleted_at is null
							inner join unidades u on s.unidad_id=u.id and u.deleted_at is null and u.periodo_id=?
							inner join asignaturas a on a.id=u.asignatura_id and a.deleted_at is null 
							inner join grupos g on g.id=a.grupo_id and g.deleted_at is null and g.year_id=?
							group by g.id)n
						inner join
							(SELECT max(nf.updated_at) as updated_at, g.id as grupo_id, g.nombre, g.abrev
							FROM notas_finales nf
							inner join asignaturas a on nf.asignatura_id=a.id and nf.periodo_id=? and nf.updated_at and a.deleted_at is null
							inner join grupos g on g.id=a.grupo_id and g.deleted_at is null and g.year_id=?
							group by g.id)nf
						ON n.grupo_id=nf.grupo_id and n.updated_at>nf.updated_at';	
						
			$grupos_desactualizados = DB::select($consulta, [$periodos[$i]->id, $user->year_id, $periodos[$i]->id, $user->year_id]);
			if (count($grupos_desactualizados) > 0) {
				$periodos[$i]->grupos = $grupos_desactualizados;
			}else{
				unset($periodos[$i]);
			}
			
		}
		return $periodos;
	}





}