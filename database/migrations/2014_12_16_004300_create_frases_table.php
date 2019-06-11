<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFrasesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('frases', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->text('frase');
			$table->string('tipo_frase');
			$table->integer('year_id')->unsigned();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('frases', function(Blueprint $table) {
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
		Schema::drop('frases');
	}

}
