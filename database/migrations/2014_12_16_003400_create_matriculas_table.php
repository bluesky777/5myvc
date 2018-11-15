<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMatriculasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('matriculas', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('alumno_id')->unsigned();
			$table->integer('grupo_id')->unsigned();
			$table->string('estado', 4)->default('MATR'); // MATR, ASIS, RETI, DESE, TRAS, PREM, FORM,      == Matriculado, Asistente, Retirado, Desertor, Trasladado, Prematriculado, Formulario
			$table->date('prematriculado')->nullable(); // Cuando fue prematriculado
			$table->date('fecha_retiro')->nullable(); // Cuando fue retirado o desertado
			$table->date('fecha_matricula')->nullable(); // Cuando por fin lo matricularon
			$table->date('fecha_pension')->nullable(); // Fecha hasta la que vale su deuda
			$table->string('razon_retiro')->default('')->nullable(); // Razón por la cual se retira o es expulsado
			$table->string('programar')->nullable(); // 'MATRIC CONDICIONAL', 'COMPROM ACADÉMICO', 'COMPROM DISCIPLINARIO', 'PERDIDA DE CUPO', 'CAMBIO INSTITUCIÓN', 'OTRO'     == Lo que se recomienda 
			//$table->string('programar_estado')->nullable(); // 'SUPERADA', 'EN PROCESO', 'NO CUMPLIDA'
			$table->text('descripcion_recomendacion')->nullable(); // Descripción del compromiso o por qué perdió cupo...
			$table->string('efectuar_una')->default('')->nullable(); // 'MATR CONDICIONAL', 'COMPR ACADEM' 
			$table->text('descripcion_efectuada')->nullable(); 
			$table->boolean('profes_editar_notas')->nullable(); // Si true, los profes pueden editar sus notas
			$table->boolean('nuevo')->nullable(); // Si true, fue creado este año
			$table->boolean('repitente')->default(0); // Si true, es porque perdió el año pasado aquí en la institución   
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
		Schema::table('matriculas', function(Blueprint $table) {
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
			$table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('matriculas');
	}

}
