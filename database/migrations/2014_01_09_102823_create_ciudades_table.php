<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCiudadesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ciudades', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('ciudad');
			$table->string('departamento')->nullable();
			$table->integer('pais_id')->unsigned()->nullable()->default(1);
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('ciudades', function(Blueprint $table) {
			$table->foreign('pais_id')->references('id')->on('paises')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ciudades');
	}

}
