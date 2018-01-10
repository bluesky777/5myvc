<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAsignaturasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('asignaturas', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('materia_id')->unsigned();
			$table->integer('grupo_id')->unsigned();
			$table->integer('profesor_id')->unsigned()->nullable();
			$table->integer('nuevo_responsable_id')->unsigned()->nullable(); // Profesor del nuevo año que va a recuperar las notas de esta asignatura
			$table->integer('creditos')->nullable();
			$table->integer('orden')->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('asignaturas', function(Blueprint $table) {
			$table->foreign('materia_id')->references('id')->on('materias')->onDelete('cascade');
			$table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('cascade');
			$table->foreign('profesor_id')->references('id')->on('profesores')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('asignaturas');
	}

}
