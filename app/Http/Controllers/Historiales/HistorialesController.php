<?php namespace App\Http\Controllers\Historiales;

use App\Http\Controllers\Controller;

use Request;
use DB;

use App\Models\User;
use App\Models\Year;
use App\Models\Grupo;


class HistorialesController extends Controller {

	public function putNotaDetalle()
	{
        $user 	    = User::fromToken();
        $nota_id    = Request::input('nota_id');
		$res 	    = [];

		$consulta 	= '(SELECT b.id as bit_id, b.created_by as created_by_user_id, b.historial_id, b.created_at, b.affected_element_new_value_int as new_value, b.affected_element_old_value_int as old_value, concat(p.nombres, " ", p.apellidos) as creado_por
                            FROM bitacoras b 
                            inner join users u on u.id=b.created_by
                            inner join profesores p on p.user_id=u.id
                            where b.affected_element_type="Nota" and b.affected_element_id=?)
                        UNION 
                        (SELECT b.id as bit_id, b.created_by as created_by_user_id, b.historial_id, b.created_at, b.affected_element_new_value_int as new_value, b.affected_element_old_value_int as old_value, u.username as creado_por
                            FROM bitacoras b 
                            inner join users u on u.id=b.created_by AND u.tipo<>"Profesor"
                            where b.affected_element_type="Nota" and b.affected_element_id=?)';
		

        $bita = DB::select($consulta, [$nota_id, $nota_id] );
        
        
		$consulta 	= 'SELECT *, concat(p.nombres, " ", p.apellidos) as creado_por, u2.username as modificado_por
                        FROM notas n 
                        inner join users u on u.id=n.created_by
                        inner join profesores p on p.user_id=u.id
                        left join users u2 on u2.id=n.updated_by
                        where n.id=?';
		

		$nota = DB::select($consulta, [$nota_id] )[0];


		$res['cambios'] 	= $bita;
		$res['nota'] 	    = $nota;
		

		return $res;
	}
	
	
	




}