<?php namespace App\Http\Controllers\Perfiles;

use App\Http\Controllers\Controller;

use Request;
use DB;
use File;
use Image;
use \stdClass;

use App\Models\User;
use App\Models\ImageModel;
use App\Models\Year;
use App\Models\Alumno;
use App\Models\Profesor;
use App\Models\Acudiente;
use App\Models\ChangeAsked;
use App\Models\Debugging;
use App\Http\Controllers\Perfiles\Publicaciones;

use Carbon\Carbon;


class PublicacionesController extends Controller {

    public function getUltimas(){
        # Las publicaciones
        $publicaciones = Publicaciones::ultimas_publicaciones('Todos');
        return $publicaciones;
    }
    

	public function putStore()
	{
        $user   = User::fromToken();
        $now 	= Carbon::now('America/Bogota');
        
        $publi_para             = Request::input('publi_para');
        $para_alumnos           = 0;
        $para_acudientes        = 0;
        $para_profes            = 0;
        $para_administradores   = 0;

		if ($publi_para == "publi_para_todos"){
            $para_todos             = 1;
            $para_alumnos           = 1;
            $para_acudientes        = 1;
            $para_profes            = 1;
            $para_administradores   = 1;
        }elseif($publi_para == "publi_privada") {
            $para_todos             = 0;
            
            if (Request::input('publi_para_alumnos'))   $para_alumnos = 1;
            if (Request::input('para_acudientes'))      $para_acudientes = 1;
            if (Request::input('para_profes'))          $para_profes = 1;
            if (Request::input('para_administradores')) $para_administradores = 1;

		}
        
        $imagen         = Request::input('imagen');
        $imagen_id      = null;
        $imagen_nom     = null;
        if ($imagen) {
            $imagen_id  = $imagen['id'];
            $imagen_nom = $imagen['nombre'];
        }
        
        $consulta = 'INSERT INTO publicaciones(persona_id, tipo_persona, contenido, imagen_id, imagen_nombre, para_todos, para_alumnos, para_acudientes, para_profes, para_administradores, created_at, updated_at) 
                    VALUES(:persona_id, :tipo_persona, :contenido, :imagen_id, :imagen_nombre, :para_todos, :para_alumnos, :para_acudientes, :para_profes, :para_administradores, :created_at, :updated_at)';
        DB::insert($consulta, [
            ':persona_id' 	        => $user->persona_id, 
            ':tipo_persona' 	    => $user->tipo, 
            ':contenido' 	        => Request::input('contenido'), 
            ':imagen_id' 	        => $imagen_id, 
            ':imagen_nombre' 	    => $imagen_nom, 
            ':para_todos' 	        => $para_todos, 
            ':para_alumnos' 	    => $para_alumnos, 
            ':para_acudientes'	    => $para_acudientes, 
            ':para_profes'	        => $para_profes, 
            ':para_administradores'	=> $para_administradores, 
            ':created_at' 	        => $now,
            ':updated_at' 	        => $now
        ]);
        
        $last_id = DB::getPdo()->lastInsertId();

		return ['publicacion_id' => $last_id];
	}


    
    
	public function putComentar()
	{
        $user   = User::fromToken();
        $now 	= Carbon::now('America/Bogota');
        
        $consulta = 'INSERT INTO comentarios(publicacion_id, persona_id, tipo_persona, comentario, created_at, updated_at) 
            VALUES (:publicacion_id, :persona_id, :tipo_persona, :comentario, :created_at, :updated_at)';
        
        DB::insert($consulta, [
            ':publicacion_id' 	   => Request::input('publi_id'), 
            ':persona_id'          => $user->persona_id, 
            ':tipo_persona' 	   => $user->tipo, 
            ':comentario' 	       => Request::input('comentario'), 
            ':created_at' 	       => $now, 
            ':updated_at' 	       => $now, 
        ]);
        
        $last_id = DB::getPdo()->lastInsertId();

		return ['comentario_id' => $last_id];
    }

    
    
    
	public function putDelete()
	{
        $user   = User::fromToken();
        $now 	= Carbon::now('America/Bogota');
        
        $consulta = 'UPDATE publicaciones SET deleted_at=:deleted_at, deleted_by=:deleted_by WHERE id=:id';
        DB::update($consulta, [
            ':deleted_at' 	        => $now, 
            ':deleted_by'           => $user->user_id, 
            ':id' 	                => Request::input('publi_id'), 
        ]);
        return 'Eliminada';
    }

    
	public function putRestaurar()
	{
        $user   = User::fromToken();
        $now 	= Carbon::now('America/Bogota');
        
        $consulta = 'UPDATE publicaciones SET deleted_at=null, updated_at=:updated_at WHERE id=:id';
        DB::update($consulta, [
            ':updated_at' 	        => $now,
            ':id' 	                => Request::input('publi_id'), 
        ]);
        return 'Restaurada';
    }


}