<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGruposTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('grupos', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('nombre');
			$table->string('abrev')->nullable();
			$table->integer('year_id')->unsigned();
			$table->integer('titular_id')->unsigned()->nullable();
			$table->integer('grado_id')->unsigned();
			$table->integer('valormatricula')->nullable();
			$table->integer('valorpension')->nullable();
			$table->integer('orden')->nullable();
			$table->boolean('caritas')->default(false);
			$table->integer('cupo')->default(20); // Cuantos alumnos se pueden matricular
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('grupos', function(Blueprint $table) {
			$table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
			$table->foreign('titular_id')->references('id')->on('profesores')->onDelete('cascade');
			$table->foreign('grado_id')->references('id')->on('grados')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('grupos');
	}

}
