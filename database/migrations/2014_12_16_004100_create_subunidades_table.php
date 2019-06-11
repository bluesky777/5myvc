<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubunidadesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subunidades', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->text('definicion');
			$table->integer('porcentaje')->default(0)->nullable();
			$table->integer('unidad_id')->unsigned();
			$table->integer('nota_default')->nullable()->dafault(0);
			$table->boolean('obligatoria')->nullable()->default(false);
			$table->integer('orden')->nullable();
			$table->boolean('por_defecto')->nullable()->default(false);
			$table->dateTime('inicia_at')->nullable();
			$table->dateTime('finaliza_at')->nullable();
			$table->integer('actividad_id')->nullable()->unsigned(); // No lo voy a relacionar para evitar problemas
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('subunidades', function(Blueprint $table) {
			$table->foreign('unidad_id')->references('id')->on('unidades')->onDelete('cascade');
		});

		
		// Subunidades que aparecerán automáticamente
		Schema::create('subunidades_por_defecto', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('definicion');
			$table->integer('porcentaje')->default(0)->nullable();
			$table->integer('unidad_defec_id')->unsigned();
			$table->integer('nota_default')->nullable()->dafault(90);
			$table->boolean('obligatoria')->nullable()->default(false);
			$table->integer('orden')->nullable();
			$table->dateTime('inicia_at')->nullable();
			$table->dateTime('finaliza_at')->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('subunidades_por_defecto', function(Blueprint $table) {
			$table->foreign('unidad_defec_id')->references('id')->on('unidades_por_defecto')->onDelete('cascade');
		});

		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subunidades_por_defecto');
		Schema::drop('subunidades');
	}

}
