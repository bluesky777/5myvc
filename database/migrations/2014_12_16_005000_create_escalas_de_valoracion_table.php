<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEscalasDeValoracionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('escalas_de_valoracion', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('desempenio');
			$table->string('valoracion');
			$table->integer('porc_inicial');
			$table->integer('porc_final');
			$table->text('descripcion')->nullable();
			$table->integer('orden');
			$table->boolean('perdido');
			$table->integer('year_id')->unsigned();
			$table->string('icono_infantil')->nullable();
			$table->string('icono_adolescente')->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('escalas_de_valoracion', function(Blueprint $table) {
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
		Schema::drop('escalas_de_valoracion');
	}

}
