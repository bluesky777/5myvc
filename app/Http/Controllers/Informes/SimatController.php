<?php namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;

use Request;
use DB;
use Excel;

use App\Models\User;
use App\Models\Year;
use App\Models\Matricula;
use App\Http\Controllers\Alumnos\OperacionesAlumnos;


class SimatController extends Controller {

	public function getIndex()
	{
        return 'Holaa';


    }


	public function getAlumnos()
	{


		Excel::create('Alumnos con acudientes', function($excel) {

            $consulta = 'SELECT g.id, g.nombre, g.abrev, g.orden, gra.orden as orden_grado, g.grado_id, g.year_id, g.titular_id,
                p.nombres as nombres_titular, p.apellidos as apellidos_titular, p.titulo,
                g.created_at, g.updated_at, gra.nombre as nombre_grado 
                from grupos g
                inner join grados gra on gra.id=g.grado_id and g.year_id=:year_id 
                left join profesores p on p.id=g.titular_id
                where g.deleted_at is null
                order by g.orden';

            $grupos = DB::select($consulta, [':year_id'=>3] );
            
            for ($i=0; $i < count($grupos); $i++) { 
                $grupo = $grupos[$i];

                $excel->sheet($grupos[$i]->abrev, function($sheet) use ($grupo) {
                    
                    $consulta   = Matricula::$consulta_asistentes_o_matriculados_simat;
                    $alumnos    = DB::select($consulta, [ ':grupo_id' => $grupo->id ] );

                    $opera = new OperacionesAlumnos;
                    $opera->recorrer_y_dividir_nombres($alumnos);
                    
                    $sheet->loadView('simat', compact('alumnos', 'grupo') )->mergeCells('A1:E1');
                    $sheet->setStyle([
                        'borders' => [
                            'allborders' => [
                                'color' => [
                                    'rgb' => '000000'
                                ]
                            ]
                        ]
                    ]);
                });

            }

            
        
        })->download('xls', ['Access-Control-Allow-Origin' => '*']);


    }


}