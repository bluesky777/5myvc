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
			$table->integer('asked_for_user_id')->nullable(); // Cuando es una petición a un usuario específico
			
			$table->integer('data_id')->nullable(); // Datos personales a cambiar
			$table->integer('assignment_id')->nullable(); // Datos a cambiar

			$table->integer('comentario_pedido')->nullable(); // Comentario con el que la persona aclara lo que está pidiendo
			
			$table->integer('main_image_id')->nullable(); // Cuando propone una imagen de usuario
			$table->integer('oficial_image_id')->nullable(); // Cuando propone una imagen oficial
			
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


		Schema::create('change_asked_data', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('oficial_image_id')->nullable(); // Cuando propone una imagen oficial
			$table->string('no_matricula')->nullable();
			$table->string('nombres')->nullable();
			$table->string('apellidos')->nullable();
			$table->string('sexo', 1)->nullable();
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
			$table->string('estrato')->nullable();
			$table->integer('ciudad_resid')->unsigned()->nullable(); // Cuidad de residencia
			$table->string('religion')->nullable();
			$table->string('email')->nullable();
			$table->string('facebook')->nullable(); // Si no asigna facebook, se tomará el email
			$table->integer('foto_id')->nullable();
			$table->integer('image_to_delete_id')->nullable(); // Pedir que alguien borre la imagen de alguien
			$table->boolean('pazysalvo')->nullable()->default(true);
			$table->integer('deuda')->nullable()->default(0);
			$table->integer('created_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::create('change_asked_assignment', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');

			$table->integer('nota_id')->nullable(); // Cuando un alumno pide el cambio de una nota
			$table->integer('nota_old')->nullable(); // Cuando propone cambio de nota, esta es la nota que anterior, que NO desea
			$table->integer('nota_new')->nullable(); // Cuando propone cambio de nota, esta es la nota que desea

			$table->integer('nota_comport_id')->nullable(); // Cuando un alumno pide el cambio de la nota de comportamiento
			$table->integer('nota_comport_old')->nullable(); // Nota anterior
			$table->integer('nota_comport_new')->nullable(); // Nota exigida

			$table->integer('frase_asignat_id')->nullable(); // Cuando alguien pide cambio de frase en su asignatura
			$table->integer('frase_asignat_descargo')->nullable(); // Comentario de por qué pide cambio de esa frase
			
			$table->integer('defini_comport_id')->nullable(); // Cuando alguien pide cambio en una frase o definición de comportamiento
			$table->integer('defini_comport_descargo')->nullable(); // Comentario de por qué pide cambio de esa frase
			
			$table->integer('materia_to_remove_id')->nullable(); // Cuando un profesor pide una materia
			$table->integer('materia_to_add_id')->nullable(); // Cuando es una petición a un usuario específico
			
			$table->integer('materia_id')->nullable();
			$table->integer('old_creditos')->nullable();
			$table->integer('new_creditos')->nullable();
			
			$table->timestamps();
		});

		Schema::table('change_asked_data', function(Blueprint $table) {
			$table->foreign('ciudad_nac')->references('id')->on('ciudades')->nullable();
			$table->foreign('ciudad_doc')->references('id')->on('ciudades')->nullable();
			$table->foreign('ciudad_resid')->references('id')->on('ciudades')->nullable();
			$table->foreign('tipo_doc')->references('id')->on('tipos_documentos')->nullable();
		});


	}



	public function down()
	{
		Schema::drop('change_asked_assignment');
		Schema::drop('change_asked_data');
		Schema::drop('change_asked');
	}

}
