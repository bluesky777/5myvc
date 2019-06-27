<?php namespace App\Http\Controllers\Informes;

use Request;
use DB;

use App\Models\User;
use App\Models\Year;
use App\Models\Grupo;
use App\Models\Area;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use \Log;


class ActasEvaluacionController extends Controller {


	private $periodos = [];


	public function putActaEvaluacionPromocion()
	{
		$user = User::fromToken();

		$year 			= Year::datos_basicos($user->year_id);
		
		$consulta = 'SELECT g.id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
			p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
			g.created_at, g.updated_at, gra.nombre as nombre_grado 
			from grupos g
			inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
			left join profesores p on p.id=g.titular_id
			where g.deleted_at is null
			order by g.orden';

		$grupos = DB::select($consulta, [':year_id'=>$user->year_id] );


		for ($i=0; $i < count($grupos); $i++) { 
			

			
			$consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, a.egresado,
					a.fecha_nac, a.ciudad_nac, c1.departamento as departamento_nac_nombre, c1.ciudad as ciudad_nac_nombre, a.tipo_doc, t1.tipo as tipo_doc_name, a.documento, a.ciudad_doc, 
					a.tipo_sangre, a.eps, CONCAT(a.telefono, " / ", a.celular) as telefonos, 
					a.direccion, a.barrio, a.estrato, a.ciudad_resid, a.religion, a.email, a.facebook, a.created_by, a.updated_by,
					a.pazysalvo, a.deuda, m.grupo_id, a.is_urbana, IF(a.is_urbana, "Urbano", "Rural") as es_urbana,
                    a.created_by, u2.username as creado_por,
					t1.tipo as tipo_doc, t1.abrev as tipo_doc_abrev,
					u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
					u.username, u.is_superuser, u.is_active,
					a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre,
					m.fecha_retiro as fecha_retiro, m.estado, m.fecha_matricula, m.nuevo, IF(m.nuevo, "SI", "NO") as es_nuevo, m.repitente,
                    m.promovido, m.promedio, m.cant_asign_perdidas, m.cant_areas_perdidas, m.anios_in_cole, 
					a.has_sisben, a.nro_sisben, a.has_sisben_3, a.nro_sisben_3 
				FROM alumnos a 
				inner join matriculas m on a.id=m.alumno_id and m.grupo_id=:grupo_id and (m.estado="MATR" or m.estado="PREM" or m.estado="DESE" or m.estado="RETI")
				left join users u on a.user_id=u.id and u.deleted_at is null
                left join users u2 on a.created_by=u.id and u2.deleted_at is null
				left join images i on i.id=u.imagen_id and i.deleted_at is null
				left join tipos_documentos t1 on t1.id=a.tipo_doc and t1.deleted_at is null
				left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
				left join ciudades c1 on c1.id=a.ciudad_nac and c1.deleted_at is null
				where a.deleted_at is null and m.deleted_at is null
				order by a.apellidos, a.nombres';
			
			$grupos[$i]->alumnos = DB::select($consulta, [':grupo_id' => $grupos[$i]->id]);

			
			// Recorro para calcular edad
			$cantA = count($grupos[$i]->alumnos);

			for ($j=0; $j < $cantA; $j++) { 
				// Edad
				if ($grupos[$i]->alumnos[$j]->fecha_nac) {
					$anio 	= date('Y', strtotime( $grupos[$i]->alumnos[$j]->fecha_nac) );
					$mes 	= date('m', strtotime( $grupos[$i]->alumnos[$j]->fecha_nac) );
					$dia 	= date('d', strtotime( $grupos[$i]->alumnos[$j]->fecha_nac) );
					$grupos[$i]->alumnos[$j]->edad = Carbon::createFromDate($anio, $mes, $dia)->age;
				}else{
					$grupos[$i]->alumnos[$j]->edad = '';
				}
				
				// Promedio
				if ($grupos[$i]->alumnos[$j]->promedio > 0) {
					$grupos[$i]->alumnos[$j]->promedio = round($grupos[$i]->alumnos[$j]->promedio, 1);
				}else{
					$grupos[$i]->alumnos[$j]->promedio = '';
				}

				// Promovido?
				if ($grupos[$i]->alumnos[$j]->promovido == 0) {
					$grupos[$i]->alumnos[$j]->promovido = 'No';
				}else{
					$grupos[$i]->alumnos[$j]->promovido = 'Si';
				}
				
			}

		}


		return [ 'grupos' => $grupos, 'year' => $year];
	}




	public function putDetalle()
	{
		$user 			= User::fromToken();
		$alumno_id 		= Request::input('alumno_id');
        

        $consulta = 'SELECT m.id as matricula_id, m.alumno_id, a.no_matricula, a.nombres, a.apellidos, a.sexo, a.user_id, 
                a.fecha_nac, a.ciudad_nac, a.celular, a.direccion, a.religion, t.tipo as tipo_doc, t.abrev as tipo_doc_abrev, a.documento, a.no_matricula, 
                m.grupo_id, m.estado, m.nuevo, m.repitente, username, a.created_at, 
                u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
                a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre
            FROM alumnos a 
            inner join matriculas m on a.id=m.alumno_id and m.grupo_id=?
            left join users u on a.user_id=u.id and u.deleted_at is null
            left join tipos_documentos t on a.tipo_doc=t.id and t.deleted_at is null
            left join images i on i.id=u.imagen_id and i.deleted_at is null
            left join images i2 on i2.id=a.foto_id and i2.deleted_at is null
            where a.deleted_at is null and m.deleted_at is null
            order by a.apellidos, a.nombres';

        $alumnos = DB::select($consulta, [ Request::input('grupo_id') ]);

		// Años de estadía
		$consulta = 'SELECT y.year, m.*, g.nombre, m.id as matricula_id
			FROM matriculas m
			INNER JOIN alumnos a ON a.id=m.alumno_id and m.deleted_at is null and a.deleted_at is null
			INNER JOIN grupos g ON g.id=m.grupo_id and g.deleted_at is null
			INNER JOIN years y ON g.year_id=y.id and y.deleted_at is null
			WHERE a.id=? order by y.year';

		$anios = DB::select($consulta, [$alumno_id]);

		
        return [ 'alumnos' => $alumnos, 'matriculas' => $anios ];
    }





	// Calcular y almacenar datos
	public function putCalcularDatos()
	{
        $user       = User::fromToken();
        $grupo_id   = Request::input('grupo_id');
        
		$escalas_val    = DB::select('SELECT * FROM escalas_de_valoracion WHERE year_id=? AND deleted_at is null', [$user->year_id]);
		$year			= Year::datos($user->year_id);
		$alumnos		= Grupo::alumnos($grupo_id, true);
		
		
		$year->periodos = DB::select('SELECT * FROM periodos WHERE year_id=? and deleted_at is null', [$user->year_id]);


		foreach ($alumnos as $alumno) {

			// Todas las materias con sus unidades y subunides
			$this->definitivasMateriasXPeriodo($alumno, $grupo_id, $user->year_id, $year->periodos, $escalas_val );

			// Recuperaciones para restar
			$consulta = 'SELECT r.*, m.materia, m.alias 
                FROM recuperacion_final r 
				INNER JOIN asignaturas a ON a.id=r.asignatura_id and a.deleted_at is null
				INNER JOIN materias m ON m.id=a.materia_id and m.deleted_at is null
				WHERE alumno_id=? and year=?';
				
			$alumno->recuperaciones = DB::select($consulta, [$alumno->alumno_id, $user->year]);
			$alumno->cant_lost_asig = $alumno->cant_lost_asig - count($alumno->recuperaciones);


			// Años de estadía
			$consulta = 'SELECT y.year
				FROM matriculas m
				INNER JOIN alumnos a ON a.id=m.alumno_id and m.deleted_at is null and a.deleted_at is null
				INNER JOIN grupos g ON g.id=m.grupo_id and g.deleted_at is null
				INNER JOIN years y ON g.year_id=y.id and y.deleted_at is null
				WHERE a.id=?
				group by y.year';

			$anios = DB::select($consulta, [$alumno->alumno_id]);
			$anios = count($anios);


			// PROMOVIDO?
			if ($year->cant_areas_pierde_year > 0 && $alumno->cant_lost_areas >= $year->cant_areas_pierde_year) {
				$alumno->promovido = 0;
			}else if ($year->cant_asignatura_pierde_year > 0 && $alumno->cant_lost_asig >= $year->cant_asignatura_pierde_year){
				$alumno->promovido = 0;
			}else{
				$alumno->promovido = 1;
			}
			

			// GUARDAMOS LOS CÁLCULOS
			$consulta = 'UPDATE matriculas 
				SET promovido=?, promedio=?, cant_asign_perdidas=?, cant_areas_perdidas=?, anios_in_cole=? 
                WHERE id=?';

            DB::update($consulta, [$alumno->promovido, $alumno->promedio, $alumno->cant_lost_asig, $alumno->cant_lost_areas, $anios, $alumno->matricula_id]);
        
        }

        


		return ['alumnos' => 'Calculados'];
    }


	public function definitivasMateriasXPeriodo(&$alumno, $grupo_id, $year_id, $periodos, $escalas_val)
	{

		$alumno->asignaturas	= Grupo::detailed_materias($grupo_id);

		$alumno->promedio = 0;
		$alumno->cant_lost_asig = 0;
		
		foreach ($alumno->asignaturas as $asignatura) {
						
			$consulta = 'SELECT (SUM(nf.nota)/4) as DefMateria
						FROM notas_finales nf
						INNER JOIN periodos p on p.year_id=:year_id and p.id=nf.periodo_id and p.deleted_at is null
						WHERE nf.alumno_id=:alumno_id and nf.asignatura_id=:asignatura_id
						GROUP BY nf.asignatura_id';
					
            $paramentros = [
                ':year_id'		=> $year_id,
                ':alumno_id'	=> $alumno->alumno_id, 
                ':asignatura_id'=> $asignatura->asignatura_id
            ];
			
			$defi = DB::select($consulta, $paramentros);
			if (count($defi) > 0) {
				$asignatura->nota_asignatura = $defi[0]->DefMateria;
			}else{
				$asignatura->nota_asignatura = 0;
			}
			

			$asignatura->promedio           = $asignatura->nota_asignatura;
            $alumno->promedio               += $asignatura->promedio;

			// Si es un promedio perdido, debo sumarlo como una asignatura perdida
			if (round($asignatura->promedio) < User::$nota_minima_aceptada) {
				$alumno->cant_lost_asig += 1;
			}

		}


		if (count($alumno->asignaturas) > 0) {
			$alumno->promedio = $alumno->promedio / count($alumno->asignaturas);
		}else{
			$alumno->promedio = 0;
		}

		
		// Agrupamos por áreas
		$areas = Area::agrupar_asignaturas($grupo_id, $alumno->asignaturas, $escalas_val);		
		$cant_lost_areas = 0;
		
		for ($k=0; $k < count($areas); $k++) { 
			if ($areas[$k]->area_nota < User::$nota_minima_aceptada){
				$cant_lost_areas = $cant_lost_areas + 1;
			}
		}
		
		$alumno->areas 				= $areas;
        $alumno->cant_lost_areas 	= $cant_lost_areas;
        

		return $alumno;
	}




}