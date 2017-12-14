<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAusenciasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ausencias', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('asignatura_id')->unsigned()->nullable();
			$table->integer('alumno_id')->unsigned()->nullable();
			$table->integer('periodo_id')->unsigned()->nullable();
			$table->integer('cantidad_ausencia')->nullable();
			$table->integer('cantidad_tardanza')->nullable();
			$table->boolean('entrada')->default(false);
			$table->string('tipo', 100)->nullable(); // ausencia o tardanza
			$table->dateTime('fecha_hora')->nullable();
			$table->string('uploaded', 20)->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('ausencias', function(Blueprint $table) {
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
			$table->foreign('periodo_id')->references('id')->on('periodos')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ausencias');
	}

}
