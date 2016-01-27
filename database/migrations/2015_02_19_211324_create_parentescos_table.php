<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateParentescosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('parentescos', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('acudiente_id');
			$table->integer('alumno_id');
			$table->string('parentesco');
			$table->string('observaciones');
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
		Schema::drop('parentescos');
	}

}
