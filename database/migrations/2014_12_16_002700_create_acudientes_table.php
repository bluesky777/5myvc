<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAcudientesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('acudientes', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('nombres');
			$table->string('apellidos')->nullable();
			$table->string('sexo')->default('M');
			$table->integer('user_id')->unsigned()->nullable();
			$table->boolean('is_acudiente')->default(false);
			$table->date('fecha_nac')->nullable();
			$table->integer('ciudad_nac')->nullable()->unsigned();
			$table->integer('foto_id')->nullable();
			$table->string('telefono')->nullable();
			$table->string('celular')->nullable();
			$table->string('ocupacion')->nullable();
			$table->string('email')->nullable();
			$table->string('barrio')->nullable();
			$table->string('direccion')->nullable();
			$table->integer('tipo_doc')->nullable()->unsigned();
			$table->string('documento')->nullable();
			$table->integer('ciudad_doc')->nullable()->unsigned();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('acudientes', function(Blueprint $table) {
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('ciudad_nac')->references('id')->on('ciudades')->onDelete('cascade');
			$table->foreign('tipo_doc')->references('id')->on('tipos_documentos')->onDelete('cascade');
			$table->foreign('ciudad_doc')->references('id')->on('ciudades')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('acudientes');
	}

}
