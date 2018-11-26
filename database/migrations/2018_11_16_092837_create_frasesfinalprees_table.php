<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFrasesfinalpreesTable extends Migration {


	public function up()
	{

		Schema::create('frases_preescolar', function(Blueprint $table) {
            $table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('asignatura_id')->unsigned();
            $table->text('definicion')->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });
		Schema::table('frases_preescolar', function(Blueprint $table) {
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
		});



	}

	public function down()
	{
		Schema::drop('frases_preescolar');
	}

}
