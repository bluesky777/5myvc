<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotaComportamientoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nota_comportamiento', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('alumno_id')->unsigned();
			$table->integer('periodo_id')->unsigned();
			$table->integer('nota');
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('nota_comportamiento', function(Blueprint $table) {
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
		Schema::drop('nota_comportamiento');
	}

}
