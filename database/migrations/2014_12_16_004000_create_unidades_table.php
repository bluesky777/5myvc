<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnidadesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('unidades', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('definicion');
			$table->integer('porcentaje')->default(0)->nullable();
			$table->integer('periodo_id')->unsigned();
			$table->integer('asignatura_id')->unsigned();
			$table->integer('orden')->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('unidades', function(Blueprint $table) {
			$table->foreign('periodo_id')->references('id')->on('periodos')->onDelete('cascade');
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('unidades');
	}

}
