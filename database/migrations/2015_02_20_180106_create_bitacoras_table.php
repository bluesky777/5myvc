<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBitacorasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bitacoras', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('created_by');								// Id del usuario que realizó la acción
			$table->string('descripcion')->nullable();					// Detalles humanamente claros de la acción realizada
			
			$table->integer('affected_user_id')->nullable();			// Usuario sobre el cual se realizó la ación
			$table->integer('affected_person_id')->nullable();			// Código de persona(alumno, profe, etc) sobre el cual se realizó la ación
			$table->string('affected_person_name')->nullable();		// Tal vez pueda poner el nombre
			$table->string('affected_person_type')->nullable();			// tipo: Al, Pr, Ac, Us
			
			$table->string('affected_element_type')->nullable();		// Puede ser la 'Nota' de un alumno
			$table->integer('affected_element_id')->nullable();			// Si fue una nota afectada, aquí ponemos su id
			$table->string('affected_element_new_value_string')->nullable();	// qué valor se puso al elemento afectado (la nota)
			$table->string('affected_element_old_value_string')->nullable();	// qué valor tenía antes de la acción
			$table->integer('affected_element_new_value_int')->nullable();	// qué valor se puso al elemento afectado (la nota)
			$table->integer('affected_element_old_value_int')->nullable();	// qué valor tenía antes de la acción
			$table->integer('periodo_id')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bitacoras');
	}

}
