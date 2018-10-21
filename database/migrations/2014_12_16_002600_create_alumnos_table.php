<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlumnosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alumnos', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('no_matricula')->nullable();
			$table->string('nombres');
			$table->string('apellidos')->nullable();
			$table->string('sexo', 1)->default('M');
			$table->integer('user_id')->unsigned()->index()->nullable();
			$table->date('fecha_nac')->nullable();
			$table->integer('ciudad_nac')->unsigned()->index()->nullable();
			$table->integer('tipo_doc')->unsigned()->index()->nullable();
			$table->string('documento')->nullable();
			$table->integer('ciudad_doc')->unsigned()->index()->nullable();
			$table->string('tipo_sangre')->nullable();
			$table->string('eps')->nullable();
			$table->string('telefono')->nullable();
			$table->string('celular')->nullable();
			$table->string('direccion')->nullable();
			$table->string('barrio')->nullable();
			$table->integer('estrato')->nullable()->default(1);
			$table->integer('ciudad_resid')->unsigned()->index()->nullable(); // Cuidad de residencia
			$table->boolean('is_urbana')->nullable()->default(true); // Zona. si False, es Rural
			$table->boolean('egresado')->nullable()->default(false); // Cuando termine Once
			$table->string('religion')->nullable();
			$table->string('email')->nullable();
			$table->string('facebook')->nullable(); // Si no asigna facebook, se tomará el email
			$table->integer('foto_id')->nullable();
			$table->boolean('pazysalvo')->nullable()->default(true);
			$table->integer('deuda')->nullable()->default(0);

			$table->string('discapacidad')->nullable(); // SV – Baja Visión. SV – Ceguera. Trastorno del Espectro Autista. DI – Cognitivo. Múltiple. Otra. SA – Usuario de LSC. SA – Usuario del Castellano. Sordoceguera. Limitación Física (Movilidad). Sistémica. Psicosocial. Voz y Habla. N/A
			$table->boolean('has_sisben')->nullable()->default(false);
			$table->integer('nro_sisben')->nullable();
			$table->boolean('has_sisben_3')->nullable()->default(false);
			$table->integer('nro_sisben_3')->nullable();
			
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		
		Schema::table('alumnos', function(Blueprint $table) {
			$table->foreign('user_id')->references('id')->on('users')->nullable();
			$table->foreign('ciudad_nac')->references('id')->on('ciudades')->nullable();
			$table->foreign('ciudad_doc')->references('id')->on('ciudades')->nullable();
			$table->foreign('ciudad_resid')->references('id')->on('ciudades')->nullable();
			$table->foreign('tipo_doc')->references('id')->on('tipos_documentos')->nullable();
			
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alumnos');
	}

}
