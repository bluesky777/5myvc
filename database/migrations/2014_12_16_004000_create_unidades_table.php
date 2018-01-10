<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnidadesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('unidades', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('definicion');
			$table->integer('porcentaje')->default(0)->nullable();
			$table->integer('periodo_id')->unsigned();
			$table->integer('asignatura_id')->unsigned();
			$table->boolean('obligatoria')->nullable()->default(false);
			$table->integer('orden')->nullable();
			$table->boolean('por_defecto')->nullable()->default(false);
			$table->date('fecha')->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('unidades', function(Blueprint $table) {
			$table->foreign('periodo_id')->references('id')->on('periodos')->onDelete('cascade');
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
		});



		// Unidades por defecto
		Schema::create('unidades_por_defecto', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('definicion');
			$table->integer('porcentaje')->default(0)->nullable();
			$table->integer('year_id')->unsigned();
			$table->boolean('obligatoria')->nullable()->default(false);
			$table->integer('orden')->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('unidades_por_defecto', function(Blueprint $table) {
			$table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
		});


	}


	public function down()
	{
		Schema::drop('unidades_por_defecto');
		Schema::drop('unidades');
	}

}
