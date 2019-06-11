<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDefinicionesComportamientoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('definiciones_comportamiento', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('comportamiento_id')->unsigned();
			$table->integer('frase_id')->unsigned()->nullable();
			$table->text('frase')->nullable();
			$table->date('fecha')->nullable();
			$table->integer('orden')->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('definiciones_comportamiento', function(Blueprint $table) {
			$table->foreign('comportamiento_id')->references('id')->on('nota_comportamiento')->onDelete('cascade');
			$table->foreign('frase_id')->references('id')->on('frases')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('definiciones_comportamiento');
	}

}
