<?php namespace App\Http\Controllers\Alumnos;



use DB;
use Carbon\Carbon;

use App\Models\User;



class Definitivas {

    public function asignaturas_docente($profe_id, $year_id){

        $consulta = 'SELECT a.id as asignatura_id, a.grupo_id, a.profesor_id, a.creditos, a.orden,
						m.materia, m.alias as alias_materia, g.nombre as nombre_grupo, g.abrev as abrev_grupo, 
						g.titular_id, g.caritas, p.id as profesor_id, p.nombres as nombres_profesor, p.apellidos as apellidos_profesor
					FROM asignaturas a 
					inner join materias m on m.id=a.materia_id 
					inner join grupos g on g.id=a.grupo_id and g.year_id=:year_id and g.deleted_at is null 
					inner join profesores p on p.id=a.profesor_id 
					where p.id=:profe_id 
					order by g.orden, a.orden';

		$asignaturas = DB::select($consulta, [':profe_id' => $profe_id,
											':year_id' => $year_id]);


		return $asignaturas;

    }


}