<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAreasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('areas', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('nombre');
			$table->string('alias')->nullable();
			$table->integer('jefe_id')->unsigned()->nullable(); // Profesor jefe de área
			$table->integer('orden')->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});


		Schema::table('areas', function(Blueprint $table) {
			$table->foreign('jefe_id')->references('id')->on('profesores')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('areas');
	}

}
