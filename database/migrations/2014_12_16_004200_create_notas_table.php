<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notas', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->bigIncrements('id');
			$table->integer('nota')->default(0);
			$table->integer('subunidad_id')->unsigned();
			$table->integer('alumno_id')->unsigned();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('notas', function(Blueprint $table) {
			$table->foreign('subunidad_id')->references('id')->on('subunidades')->onDelete('cascade');
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('notas');
	}

}
