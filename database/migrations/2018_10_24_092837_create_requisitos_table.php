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
			$table->string('estado');
			$table->text('descripcion')->nullable(); 
			$table->integer('updated_by')->unsigned()->nullable();
			$table->timestamps();
		});
		Schema::table('requisitos_alumno', function(Blueprint $table) {
            $table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});




	}

	public function down()
	{
		Schema::drop('requisitos_alumno');
		Schema::drop('requisitos_matricula');
	}

}
