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


class ExcelListadoDocentesController extends Controller {

	public function getIndex()
	{
        return 'Holaa';


    }


	public function getDocentes()
	{
        $user = User::fromToken();
        
        $host = parse_url(request()->headers->get('referer'), PHP_URL_HOST);
        if ($host == '0.0.0.0' || $host == 'localhost' || $host == '127.0.0.1') {
            $extension = 'xls';
        }else{
            $extension = 'xlsx';
        }

		Excel::create('Alumnos con acudientes '.$user->year, function($excel) {

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
                    
                    $sheet->setBorder('A3:BL'.(count($alumnos)+3), 'thin', "D8572C");
                    $sheet->getStyle('A3:BL3')->getAlignment()->setWrapText(true); 
                    $sheet->mergeCells('A2:E2');
                    
                    $this->Comentarios($sheet);
                    
                    $opera = new OperacionesAlumnos;
                    $opera->recorrer_y_dividir_nombres($alumnos);
                    
                    // Traigo los acudientes de 
		            $cantA = count($alumnos);
                    for ($i=0; $i < $cantA; $i++) { 
                        $consulta                   = Matricula::$consulta_parientes;
                        $acudientes                 = DB::select($consulta, [ $alumnos[$i]->alumno_id ]);
                        
                        if (count($acudientes) == 0) {
                            $acu1       = (object)Acudiente::$acudiente_vacio;
                            //$acu1->id   = -1;
                            array_push($acudientes, $acu1);
                            
                            $acu2       = (object)Acudiente::$acudiente_vacio;
                            //$acu2->id   = 0;
                            array_push($acudientes, $acu2);
                        }else if (count($acudientes) == 1) {
                            $acu1 = (object)Acudiente::$acudiente_vacio;
                            //$acu1->id = -1;
                            array_push($acudientes, $acu1);
                        }
                        $alumnos[$i]->acudientes    = $acudientes;
                    }
                    
                    $sheet->loadView('simat', compact('alumnos', 'grupo') )->mergeCells('A1:E1');
                    
                    //$sheet->setAutoFilter();
                    $sheet->setWidth(['A'=>5, 'B'=>5, 'C'=>10, 'D'=>11, 'E'=>10, 'F'=>16, 'P'=>13, 'Q'=>7, 'S'=>11, 'T'=>7, 'Y'=>14, 'Z'=>5, 'AA'=>7, 'X'=>10, 'AB'=>5, 'AD'=>10, 
                                        'AF'=>12, 'AG'=>12, 'AH'=>6, 'AL'=>11, 'AN'=>14, 'AU'=>17,
                                        'AW'=>12, 'AX'=>12, 'AY'=>6, 'BC'=>11, 'BE'=>14, 'BL'=>17,]);
                    $sheet->setHeight(3, 30);
                    
                });

            }

            
        
        })->download($extension, ['Access-Control-Allow-Origin' => '*']);


    }
    
    
    private function Comentarios(&$sheet){
        
        $sheet->getComment('C3')->getText()->createTextRun('Coloque: "CÉDULA", "PERMISO ESPECIAL DE PERMANENCIA", "TARJETA DE IDENTIDAD", "CÉDULA EXTRANJERA", "REGISTRO CIVIL", "NÚMERO DE IDENTIFICACIÓN PERSONAL", "NÚMERO ÚNICO DE IDENTIFICACIÓN PERSONAL", "NÚMERO DE SECRETARÍA", "PASAPORTE"');
        $sheet->getComment('E3')->getText()->createTextRun('No coloque departamento, solo ciudad');
        $sheet->getComment('K3')->getText()->createTextRun('Ignore esta columna');
        $sheet->getComment('L3')->getText()->createTextRun('Coloque: MATR, ASIS, RETI, DESE');
        $sheet->getComment('Q3')->getText()->createTextRun('¿Es urbano? SI o NO');
        $sheet->getComment('U3')->getText()->createTextRun('Coloque "No aplica" o deje vacío si no tiene el antiguo SISBEN.');
        $sheet->getComment('V3')->getText()->createTextRun('Coloque "No aplica" o deje vacío si no tiene el nuevo SISBEN tipo 3.');
        $sheet->getComment('AA3')->getText()->createTextRun('Si el año pasado NO finalizó en la institución, coloque SI, de lo contrario, especifique que NO es nuevo.');
        
        $sheet->getComment('AE3')->getText()->createTextRun('Coloque un código e ignore las demás columnas para asignar un acudiente a este alumno que ya está agregado');
        $sheet->getComment('AI3')->getText()->createTextRun('Coloque: "CÉDULA", "PERMISO ESPECIAL DE PERMANENCIA", "TARJETA DE IDENTIDAD", "CÉDULA EXTRANJERA", "REGISTRO CIVIL", "NÚMERO DE IDENTIFICACIÓN PERSONAL", "NÚMERO ÚNICO DE IDENTIFICACIÓN PERSONAL", "NÚMERO DE SECRETARÍA", "PASAPORTE"');
        $sheet->getComment('AJ3')->getText()->createTextRun('SI o NO');
        $sheet->getComment('AK3')->getText()->createTextRun('Padre, Madre, Hermano, Hermana, Abuelo, Abuela, Tío, Tía, Primo(a), Otro');
        $sheet->getComment('AM3')->getText()->createTextRun('Ignore esta columna');
        $sheet->getComment('AS3')->getText()->createTextRun('Ignore esta columna');
        $sheet->getComment('AU3')->getText()->createTextRun('Comentarios sobre este acudiente del alumno');
        
        $sheet->getComment('AV3')->getText()->createTextRun('Coloque un código e ignore las demás columnas para asignar un acudiente a este alumno que ya está agregado');
        $sheet->getComment('AZ3')->getText()->createTextRun('Coloque: "CÉDULA", "PERMISO ESPECIAL DE PERMANENCIA", "TARJETA DE IDENTIDAD", "CÉDULA EXTRANJERA", "REGISTRO CIVIL", "NÚMERO DE IDENTIFICACIÓN PERSONAL", "NÚMERO ÚNICO DE IDENTIFICACIÓN PERSONAL", "NÚMERO DE SECRETARÍA", "PASAPORTE"');
        $sheet->getComment('BA3')->getText()->createTextRun('SI o NO');
        $sheet->getComment('BB3')->getText()->createTextRun('Padre, Madre, Hermano, Hermana, Abuelo, Abuela, Tío, Tía, Primo(a), Otro');
        $sheet->getComment('BD3')->getText()->createTextRun('Ignore esta columna');
        $sheet->getComment('BJ3')->getText()->createTextRun('Ignore esta columna');
        $sheet->getComment('BL3')->getText()->createTextRun('Comentarios sobre este acudiente del alumno');
        
    }


}