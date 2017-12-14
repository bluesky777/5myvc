<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDefaultUnidadesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('default_unidades', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('definicion');
			$table->integer('porcentaje')->default(0)->nullable();
			$table->integer('orden')->nullable();
			$table->integer('year_id')->unsigned()->nullable();
			$table->integer('profesor_id')->unsigned()->nullable();
			$table->boolean('show_definicion')->dafault(true);
			$table->boolean('can_change_definicion')->dafault(true);
			$table->boolean('can_change_porcentaje')->dafault(true);
			$table->boolean('can_change_orden')->dafault(true);
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->timestamps();
		});
		Schema::table('default_unidades', function(Blueprint $table) {
			$table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
		});




		Schema::create('default_subunidades', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('definicion');
			$table->integer('porcentaje')->default(0)->nullable();
			$table->integer('default_unidad_id')->unsigned();
			$table->integer('nota_default')->nullable();
			$table->integer('orden')->nullable();
			$table->boolean('can_change_definicion')->dafault(true);
			$table->boolean('can_change_porcentaje')->dafault(true);
			$table->boolean('can_change_orden')->dafault(true);
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->timestamps();
		});
		Schema::table('default_subunidades', function(Blueprint $table) {
			$table->foreign('default_unidad_id')->references('id')->on('default_unidades')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('default_subunidades');
		Schema::drop('default_unidades');
	}

}
