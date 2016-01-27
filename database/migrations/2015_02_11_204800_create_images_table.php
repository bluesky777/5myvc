<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('images', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('nombre'); // Si no es pública, este nombre indica una imagen dentro de la carpeta del usuario.
			$table->integer('user_id')->nullable();
			$table->boolean('publica')->nullable(); // Esto indica que la imagen no se buscará en la carpeta del usuario, sino en la carpeta del colegio.
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
		Schema::drop('images');
	}

}
