<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProfesoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('profesores', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('nombres');
			$table->string('apellidos')->nullable();
			$table->string('sexo', 1)->default('M');
			$table->integer('foto_id')->nullable(); // Código de la imagen
			$table->integer('firma_id')->nullable(); // Código de la imagen que tiene la firma
			$table->dateTime('permiso_hasta')->nullable(); // Fecha hora hasta la que permitimos que este profesor siga editando notas
			$table->integer('tipo_doc')->unsigned()->nullable();
			$table->integer('num_doc')->nullable();
			$table->integer('ciudad_doc')->unsigned()->nullable();
			$table->date('fecha_nac')->nullable();
			$table->integer('ciudad_nac')->unsigned()->nullable();
			$table->string('titulo')->nullable();
			$table->string('estado_civil')->nullable();
			$table->string('barrio')->nullable();
			$table->string('direccion')->nullable();
			$table->string('telefono')->nullable();
			$table->string('celular')->nullable();
			$table->string('facebook')->nullable();
			$table->string('email')->nullable();
			$table->string('tipo_profesor')->nullable(); // Catedrático o Tiempo completo
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('profesores', function(Blueprint $table) {
			$table->foreign('tipo_doc')->references('id')->on('tipos_documentos')->onDelete('cascade');
			$table->foreign('ciudad_doc')->references('id')->on('ciudades')->onDelete('cascade');
			$table->foreign('ciudad_nac')->references('id')->on('ciudades')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('profesores');
	}

}
