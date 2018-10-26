<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRequisitosTable extends Migration {


	public function up()
	{

		Schema::create('requisitos_matricula', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('year_id')->unsigned();
			$table->string('requisito');
			$table->text('descripcion')->nullable(); 
			$table->integer('updated_by')->unsigned()->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('requisitos_matricula', function(Blueprint $table) {
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});


		Schema::create('requisitos_alumno', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('alumno_id')->unsigned();
			$table->integer('requisito_id')->unsigned();
			$table->string('estado')->default('Falta');
			$table->text('descripcion')->nullable(); 
			$table->integer('updated_by')->unsigned()->nullable();
			$table->timestamps();
		});
		Schema::table('requisitos_alumno', function(Blueprint $table) {
            $table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
            $table->foreign('requisito_id')->references('id')->on('requisitos_matricula')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});


		Schema::create('llevo_formulario', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('alumno_id')->unsigned();
			$table->integer('year'); // Año 2018 o 2019 etc.
			$table->dateTime('llevo_formulario')->nullable(); // Cuando se llevó el formulario
			$table->timestamps();
		});
		Schema::table('llevo_formulario', function(Blueprint $table) {
            $table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
		});




	}

	public function down()
	{
		Schema::drop('llevo_formulario');
		Schema::drop('requisitos_alumno');
		Schema::drop('requisitos_matricula');
	}

}
