<?php namespace App\Http\Controllers\Alumnos;



use Request;
use DB;
use Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Year;
use App\Models\Periodo;



class ImporterFixer {

    public $tipos_doc;
    public $cant_td;
    
	
	public function __construct()
	{
        $this->tipos_doc    = DB::select('SELECT id, tipo FROM tipos_documentos WHERE deleted_at is null');
        $this->cant_td      = count($this->tipos_doc);
        $this->ciudades    	= DB::select('SELECT id, ciudad FROM ciudades WHERE deleted_at is null');
        $this->cant_ciud    = count($this->ciudades);
    }
    

	public function verificar($alumno)
	{
		$cons = '';

		if ($alumno->tipo_de_documento == 'fecha_nac')
			$valor = Carbon::parse($valor);

		// Tipo doc
		for ($i=0; $i < $this->cant_td; $i++) { 
            if($this->tipos_doc[$i]->tipo == $alumno->tipo_de_documento){
                $alumno->tipo_doc = $this->tipos_doc[$i]->id;
            }
        } 
        if(!$alumno->tipo_doc){
            $alumno->tipo_doc = null;
		}
		
		// ciudad de nac
		for ($i=0; $i < $this->cant_ciud; $i++) { 
            if($this->ciudades[$i]->ciudad == $alumno->lugar_de_expedicion_ciudad){
				$alumno->ciudad_id = $this->ciudades[$i]->id;
				$cons .= ', ciudad_doc='.$alumno->ciudad_id;
            }
		}
		
		// ciudad de nac
		for ($i=0; $i < $this->cant_ciud; $i++) { 
            if($this->ciudades[$i]->ciudad == $alumno->lugar_de_expedicion_ciudad){
				$alumno->ciudad_id = $this->ciudades[$i]->id;
				$cons .= ', ciudad_doc='.$alumno->ciudad_id;
            }
        }
		
		return ['consulta' => $cons];

	}



	public function valorAcudiente($acudiente_id, $parentesco_id, $user_acud_id, $propiedad, $valor, $user_id)
	{

		$consulta 	= '';
		$datos 		= [];
		$now 		= Carbon::now('America/Bogota');

		if ($propiedad == 'fecha_nac')
			$valor = Carbon::parse($valor);

		switch ($propiedad) {
			case 'username':
				$consulta 	= 'UPDATE users SET username=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:user_id';
				$datos 		= [ ':valor' => $valor, ':modificador' => $user_id, ':fecha' => $now, ':user_id' => $user_acud_id ];
				break;
			
			case 'parentesco':
				$consulta 	= 'UPDATE parentescos SET parentesco=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:parentesco_id';
				$datos 		= [ ':valor' => $valor, ':modificador' => $user_id, ':fecha' => $now, ':parentesco_id' => $parentesco_id ];
				break;
			
			default:
				$consulta = 'UPDATE acudientes SET '.$propiedad.'=:valor, updated_by=:modificador, updated_at=:fecha WHERE id=:acudiente_id';
				$datos 		= [
					':valor'		=> $valor, 
					':modificador'	=> $user_id, 
					':fecha' 		=> $now,
					':acudiente_id'	=> $acudiente_id
				];
				break;
		}
		
		
		$consulta = DB::raw($consulta);

		$res = DB::update($consulta, $datos);

		if($res)
			return 'Guardado';
		else
			return 'No guardado';

	}



}