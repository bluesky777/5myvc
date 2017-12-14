<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMatriculasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('matriculas', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('alumno_id')->unsigned();
			$table->integer('grupo_id')->unsigned();
			//$table->boolean('matriculado')->default(true);
			$table->string('estado', 4)->default('MATR'); // MATR, ASIS, RETI     == Matriculado, Asistente y Retirado
			$table->date('fecha_retiro')->nullable(); // Cuando fue retirado o desertado
			$table->date('fecha_matricula')->nullable(); // Cuando por fin lo matricularon
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('matriculas', function(Blueprint $table) {
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
			$table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('matriculas');
	}

}
