<?php namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;

use Request;
use DB;
use Excel;

use App\Models\User;
use App\Models\Year;
use App\Models\Matricula;
use App\Models\Acudiente;
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

            $grupos = DB::select($consulta, [':year_id'=> Year::actual()->id] );
            
            for ($i=0; $i < count($grupos); $i++) { 
                $grupo = $grupos[$i];

                $excel->sheet($grupos[$i]->abrev, function($sheet) use ($grupo) {
                    
                    $consulta   = Matricula::$consulta_asistentes_o_matriculados_simat;
                    $alumnos    = DB::select($consulta, [ ':grupo_id' => $grupo->id ] );
                    
                    $sheet->setBorder('A3:AJ'.(count($alumnos)+3), 'thin', "D8572C");
                    $sheet->mergeCells('A2:E2');
                    
                    $opera = new OperacionesAlumnos;
                    $opera->recorrer_y_dividir_nombres($alumnos);
                    
                    // Traigo los acudientes de 
		            $cantA = count($alumnos);
                    for ($i=0; $i < $cantA; $i++) { 
                        $consulta                   = Matricula::$consulta_parientes;
                        $acudientes                 = DB::select($consulta, [ $alumnos[$i]->alumno_id ]);
                        
                        if (count($acudientes) == 0) {
                            $acu1       = (object)Acudiente::$acudiente_vacio;
                            $acu1->id   = -1;
                            array_push($acudientes, $acu1);
                            
                            $acu2       = (object)Acudiente::$acudiente_vacio;
                            $acu2->id   = 0;
                            array_push($acudientes, $acu2);
                        }else if (count($acudientes) == 1) {
                            $acu1 = (object)Acudiente::$acudiente_vacio;
                            $acu1->id = -1;
                            array_push($acudientes, $acu1);
                        }
                        $alumnos[$i]->acudientes    = $acudientes;
                    }
                    
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