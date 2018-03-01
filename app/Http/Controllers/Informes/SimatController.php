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
        $host = parse_url(request()->headers->get('referer'), PHP_URL_HOST);
        if ($host == '0.0.0.0' || $host == 'localhost' || $host == '127.0.0.1') {
            $extension = 'xls';
        }else{
            $extension = 'xlsx';
        }

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
                    
                    $sheet->getComment('C3')->getText()->createTextRun('Coloque: "CÉDULA", "PERMISO ESPECIAL DE PERMANENCIA", "TARJETA DE IDENTIDAD", "CÉDULA EXTRANJERA", "REGISTRO CIVIL", "NÚMERO DE IDENTIFICACIÓN PERSONAL", "NÚMERO ÚNICO DE IDENTIFICACIÓN PERSONAL", "NÚMERO DE SECRETARÍA", "PASAPORTE"');
                    $sheet->getComment('E3')->getText()->createTextRun('No coloque departamento, solo ciudad');
                    $sheet->getComment('K3')->getText()->createTextRun('Ignore esta columna');
                    $sheet->getComment('L3')->getText()->createTextRun('Coloque: MATR, ASIS, RETI, DESE');
                    $sheet->getComment('Q3')->getText()->createTextRun('Coloque "No aplica" o deje vacío si no tiene el antiguo SISBEN.');
                    $sheet->getComment('R3')->getText()->createTextRun('Coloque "No aplica" o deje vacío si no tiene el nuevo SISBEN tipo 3.');
                    $sheet->getComment('V3')->getText()->createTextRun('Si el año pasado NO finalizó en la institución, coloque SI, de lo contrario, especifique que NO es nuevo.');
                    $sheet->getComment('AA3')->getText()->createTextRun('Coloque: "CÉDULA", "PERMISO ESPECIAL DE PERMANENCIA", "TARJETA DE IDENTIDAD", "CÉDULA EXTRANJERA", "REGISTRO CIVIL", "NÚMERO DE IDENTIFICACIÓN PERSONAL", "NÚMERO ÚNICO DE IDENTIFICACIÓN PERSONAL", "NÚMERO DE SECRETARÍA", "PASAPORTE"');
                    $sheet->getComment('AB3')->getText()->createTextRun('SI o NO');
                    $sheet->getComment('AD3')->getText()->createTextRun('Ignore esta columna');
                    $sheet->getComment('AJ3')->getText()->createTextRun('SI o NO');
                    $sheet->getComment('AL3')->getText()->createTextRun('Ignore esta columna');
                    
                    
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

            
        
        })->download($extension, ['Access-Control-Allow-Origin' => '*']);


    }


}