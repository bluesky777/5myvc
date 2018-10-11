<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePublicacionesTable extends Migration {


	public function up()
	{

		Schema::create('publicaciones', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('persona_id')->unsigned()->nullable();
			$table->string('tipo_persona'); // 1. Alumno, 2. Admin, etc.
			$table->text('contenido')->nullable(); // Solo si es de tipo Contenido
			$table->integer('imagen_id')->unsigned()->nullable();
			$table->string('imagen_nombre')->nullable();
			$table->boolean('para_todos')->nullable()->default(false);
			$table->boolean('para_alumnos')->nullable()->default(false);
			$table->boolean('para_acudientes')->nullable()->default(false);
			$table->boolean('para_profes')->nullable()->default(false);
			$table->boolean('para_administradores')->nullable()->default(false);
			$table->integer('deleted_by')->unsigned()->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('publicaciones', function(Blueprint $table) {
			$table->foreign('imagen_id')->references('id')->on('images')->onDelete('cascade');
		});


		Schema::create('comentarios', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('publicacion_id')->unsigned()->nullable();
			$table->integer('persona_id')->unsigned()->nullable();
			$table->string('tipo_persona'); // 1. Alumno, 2. Admin, etc.
			$table->string('comentario')->nullable(); // Solo si es de tipo Contenido
			$table->integer('deleted_by')->unsigned()->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('comentarios', function(Blueprint $table) {
			$table->foreign('publicacion_id')->references('id')->on('publicaciones')->onDelete('cascade');
		});






	}

	public function down()
	{
		Schema::drop('comentarios');
		Schema::drop('publicaciones');
	}

}
