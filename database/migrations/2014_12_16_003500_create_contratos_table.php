<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContratosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contratos', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('profesor_id')->unsigned()->index()->nullable();
			$table->integer('year_id')->unsigned()->index()->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('contratos', function(Blueprint $table) {
			$table->foreign('profesor_id')->references('id')->on('profesores')->onDelete('cascade');
			$table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('contratos');
	}

}
