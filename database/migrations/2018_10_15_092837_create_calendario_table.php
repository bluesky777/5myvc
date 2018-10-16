<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCalendarioTable extends Migration {


	public function up()
	{

		Schema::create('calendario', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('created_by')->unsigned()->nullable();
			$table->string('created_by_nombres')->nullable();
			$table->string('title'); 
			$table->boolean('allDay')->nullable()->default(true);
			$table->dateTime('start')->nullable();
			$table->dateTime('end')->nullable();
			$table->string('type')->nullable(); // 'cumple'
			$table->integer('cumple_alumno_id')->unsigned()->nullable();
			$table->integer('cumple_profe_id')->unsigned()->nullable();
			$table->boolean('solo_profes')->default(false);
			$table->string('url')->nullable(); // 'http://google.com/'
			$table->integer('deleted_by')->unsigned()->nullable();
			$table->integer('updated_by')->unsigned()->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('calendario', function(Blueprint $table) {
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
		});




	}

	public function down()
	{
		Schema::drop('calendario');
	}

}
