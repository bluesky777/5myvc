<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFrasesAsignaturaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('frases_asignatura', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('alumno_id');
			$table->integer('frase_id')->nullable();
			$table->string('frase')->nullable();
			$table->integer('asignatura_id');
			$table->integer('periodo_id');
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('frases_asignatura');
	}

}
