<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotasfinalesTable extends Migration {


	public function up()
	{

		Schema::create('notas_finales', function(Blueprint $table) {
            $table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('alumno_id')->unsigned()->nullable();
            $table->integer('asignatura_id')->unsigned()->nullable();
            $table->integer('periodo_id')->unsigned()->nullable();
			$table->integer('nota')->default(0);
            $table->boolean('recuperada')->nullable()->default(false);
            $table->boolean('manual')->nullable()->default(false); // Define si No fue calculada sino escrita manualmente por el docente
			$table->integer('updated_by')->nullable();
            $table->timestamps();
        });
		Schema::table('notas_finales', function(Blueprint $table) {
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
			$table->foreign('periodo_id')->references('id')->on('periodos')->onDelete('cascade');
		});






	}

	public function down()
	{
		Schema::drop('notas_finales');
	}

}
