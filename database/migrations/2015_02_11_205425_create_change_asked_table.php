<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChangeAskedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('change_asked', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('asked_by_user_id'); // Usuario que hace la petición
			$table->integer('asked_to_user_id')->nullable(); // Cuando es una petición a un usuario específico
			$table->integer('comentario_pedido')->nullable(); // Comentario con el que la persona aclara lo que está pidiendo
			
			$table->integer('main_image_id')->nullable(); // Cuando propone una imagen de usuario
			$table->integer('oficial_image_id')->nullable(); // Cuando propone una imagen de oficial
			$table->string('nombres')->nullable(); // Cuando propone actualizar nombre
			$table->string('apellidos')->nullable(); // Cuando propone actualizar apellido

			$table->integer('somebody_id')->nullable(); // Cuando propone cambiar algún dato a alguien
			$table->string('somebody_nombres')->nullable(); // Cuando propone cambiar el nombre de alguien
			$table->string('somebody_apellidos')->nullable(); // Cuando propone cambiar el apellido de alguien
			$table->integer('somebody_nota_id')->nullable(); // Cuando propone cambio de nota
			$table->integer('somebody_nota_old')->nullable(); // Cuando propone cambio de nota, esta es la nota que desea
			$table->integer('somebody_nota_new')->nullable(); // Cuando propone cambio de nota, esta es la nota que desea
			$table->integer('somebody_image_id_to_delete')->nullable(); // Cuando propone cambio de nota, esta es la nota que desea

			$table->integer('materia_to_remove_id')->nullable(); // Cuando un profesor pide una materia
			$table->integer('materia_to_add_id')->nullable(); // Cuando es una petición a un usuario específico
			$table->integer('new_creditos')->nullable();
			
			$table->integer('change_asignatura_old_id')->nullable(); // Especifíca los créditos que el profe desea para su nueva materia
			$table->integer('change_asignatura_new_id')->nullable();
			$table->integer('change_creditos')->nullable();

			$table->integer('asked_nota_id')->nullable(); // Cuando un alumno pide el cambio de una nota
			$table->integer('nota_old')->nullable(); // Cuando propone cambio de nota, esta es la nota que anterior, que NO desea
			$table->integer('nota_new')->nullable(); // Cuando propone cambio de nota, esta es la nota que desea

			$table->string('comentario_respuesta')->nullable(); // Comentario sobre el pedido que está haciendo
			$table->datetime('rechazado_at')->nullable();
			$table->datetime('accepted_at')->nullable();

			$table->integer('periodo_asked_id')->nullable(); // Periodo para el que fue hecha la petición
			$table->integer('year_asked_id')->nullable(); // Año para el que fue hecha la petición

			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
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
		Schema::drop('change_asked');
	}

}
