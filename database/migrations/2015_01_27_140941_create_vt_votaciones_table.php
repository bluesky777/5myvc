<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVtVotacionesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vt_votaciones', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('year_id')->unsigned()->nullable();
			$table->string('nombre');
			$table->boolean('votan_profes')->default(true);
			$table->boolean('votan_acudientes')->default(false);
			$table->boolean('locked')->default(false);
			$table->boolean('actual')->default(false);
			$table->boolean('in_action')->default(false);
			$table->boolean('can_see_results')->default(false);
			$table->date('fecha_inicio')->nullable();
			$table->date('fecha_fin')->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('vt_votaciones', function(Blueprint $table) {
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
		});

		Schema::create('vt_aspiraciones', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('aspiracion');
			$table->string('abrev');
			$table->integer('votacion_id')->unsigned();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('vt_aspiraciones', function(Blueprint $table) {
			$table->foreign('votacion_id')->references('id')->on('vt_votaciones')->onDelete('cascade');
		});

		Schema::create('vt_participantes', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('grupo_profes_acudientes')->nullable(); // 2, 8, 'Profesores' o 'Acudientes'
			$table->integer('votacion_id')->unsigned();
			$table->boolean('locked')->dafault(false);
			$table->integer('intentos')->default(0); # Tal vez quiera borrar sus votos para que lo vuelva a intentar, al borrar esos votos, esta celda aumentaría
			$table->timestamps();
		});
		Schema::table('vt_participantes', function(Blueprint $table) {
			$table->foreign('votacion_id')->references('id')->on('vt_votaciones')->onDelete('cascade');
		});

		Schema::create('vt_candidatos', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('aspiracion_id')->unsigned();
			$table->string('plancha')->nullable();
			$table->string('numero')->nullable();
			$table->boolean('locked')->default(false);
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('vt_candidatos', function(Blueprint $table) {
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('aspiracion_id')->references('id')->on('vt_aspiraciones')->onDelete('cascade');;
		});


		Schema::create('vt_votos', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('candidato_id')->unsigned()->nullable();
			$table->integer('blanco_aspiracion_id')->unsigned()->nullable();
			$table->boolean('locked')->default(false);
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('vt_votos', function(Blueprint $table) {
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
		Schema::drop('vt_votos');
		Schema::drop('vt_candidatos');
		Schema::drop('vt_participantes');
		Schema::drop('vt_aspiraciones');
		Schema::drop('vt_votaciones');
	}

}
