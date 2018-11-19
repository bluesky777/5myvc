<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRecuperacionfinalTable extends Migration {


	public function up()
	{

		Schema::create('recuperacion_final', function(Blueprint $table) {
            $table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('alumno_id')->unsigned()->nullable();
            $table->integer('asignatura_id')->unsigned()->nullable();
            $table->integer('year')->unsigned()->nullable();
			$table->integer('nota')->default(0);
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
		Schema::table('recuperacion_final', function(Blueprint $table) {
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
		});



	}

	public function down()
	{
		Schema::drop('recuperacion_final');
	}

}
