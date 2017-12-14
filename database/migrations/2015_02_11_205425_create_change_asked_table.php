<?php

use Illuminate\Support\Facades\Schema;
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
			$table->integer('asked_for_user_id')->nullable(); // Cuando la petición es sobre cambiarle algo a alguien más
			
			$table->integer('data_id')->nullable(); // Datos personales a cambiar
			$table->integer('assignment_id')->nullable(); // Datos a cambiar

			$table->integer('comentario_pedido')->nullable(); // Comentario con el que la persona aclara lo que está pidiendo
			
			$table->string('comentario_respuesta')->nullable(); // Comentario sobre el pedido que está haciendo
			$table->datetime('rechazado_at')->nullable();
			$table->datetime('accepted_at')->nullable();

			$table->integer('periodo_asked_id')->nullable(); // Periodo para el que fue hecha la petición
			$table->integer('year_asked_id')->nullable(); // Año para el que fue hecha la petición

			$table->integer('answered_by')->nullable(); // Usuario que se hizo cargo de aprobar o rechazar por lo menos el último cambios solicitados
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});


		Schema::create('change_asked_data', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('no_matricula')->nullable();
			$table->string('nombres_new')->nullable();
			$table->boolean('nombres_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->string('apellidos_new')->nullable();
			$table->boolean('apellidos_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->string('sexo_new', 1)->nullable();
			$table->boolean('sexo_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->date('fecha_nac_new')->nullable();
			$table->boolean('fecha_nac_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('ciudad_nac_new')->unsigned()->index()->nullable();
			$table->boolean('ciudad_nac_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('tipo_doc_new')->unsigned()->index()->nullable();
			$table->boolean('tipo_doc_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->string('documento_new')->nullable();
			$table->boolean('documento_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('ciudad_doc_new')->unsigned()->index()->nullable();
			$table->boolean('ciudad_doc_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->string('tipo_sangre_new')->nullable();
			$table->boolean('tipo_sangre_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->string('eps_new')->nullable();
			$table->boolean('eps_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->string('telefono_new')->nullable();
			$table->boolean('telefono_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->string('celular_new')->nullable();
			$table->boolean('celular_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->string('direccion_new')->nullable();
			$table->boolean('direccion_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->string('barrio_new')->nullable();
			$table->boolean('barrio_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->string('estrato_new')->nullable();
			$table->boolean('estrato_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->integer('ciudad_resid_new')->unsigned()->nullable(); // Cuidad de residencia
			$table->boolean('ciudad_resid_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->string('religion_new')->nullable();
			$table->boolean('religion_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->string('email_new')->nullable();
			$table->boolean('email_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->string('facebook_new')->nullable(); // Si no asigna facebook, se tomará el email
			$table->boolean('facebook_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->boolean('pazysalvo_new')->nullable();
			$table->boolean('pazysalvo_accepted')->nullable(); // Cuando el administrador aprueba este cambio
						
			$table->integer('foto_id_new')->nullable(); // Cuando propone una imagen de usuario
			$table->boolean('foto_id_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->integer('image_id_new')->nullable(); // Cuando propone una imagen oficial
			$table->boolean('image_id_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->integer('firma_id_new')->nullable(); // Cuando propone una imagen oficial
			$table->boolean('firma_id_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->integer('image_to_delete_id')->nullable(); // Pedir que alguien borre la imagen de alguien
			$table->boolean('image_to_delete_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('created_by')->nullable();
			$table->integer('deleted_by')->nullable();
			
			$table->timestamps();
		});

		Schema::create('change_asked_assignment', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');

			$table->integer('nota_id')->nullable(); // Cuando un alumno pide el cambio de una nota
			$table->integer('nota_new')->nullable(); // Cuando propone cambio de nota, esta es la nota que desea
			$table->boolean('nota_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('nota_comport_id')->nullable(); // Cuando un alumno pide el cambio de la nota de comportamiento
			$table->integer('nota_comport_new')->nullable(); // Nota exigida
			$table->boolean('nota_comport_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('frase_asignat_id')->nullable(); // Cuando alguien pide cambio de frase en su asignatura
			$table->boolean('frase_asignat_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('defini_comport_id')->nullable(); // Cuando alguien pide cambio en una frase o definición de comportamiento
			$table->boolean('defini_comport_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('materia_to_remove_id')->nullable(); // Cuando un profesor pide que le QUITEN una materia
			$table->boolean('materia_to_remove_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('materia_to_add_id')->nullable(); // Cuando un profesor pide que le AGREGUEN una materia
			$table->boolean('materia_to_add_accepted')->nullable(); // Cuando el administrador aprueba este cambio

			$table->integer('materia_id')->nullable(); // Cuando un profesor pide que cambien los créditos que da en una materia
			$table->integer('creditos_new')->nullable();
			$table->boolean('creditos_accepted')->nullable(); // Cuando el administrador aprueba este cambio
			
			$table->timestamps();
		});

		# Relaciones
		Schema::table('change_asked_data', function(Blueprint $table) {
			$table->foreign('ciudad_nac_new')->references('id')->on('ciudades')->nullable();
			$table->foreign('ciudad_doc_new')->references('id')->on('ciudades')->nullable();
			$table->foreign('ciudad_resid_new')->references('id')->on('ciudades')->nullable();
			$table->foreign('tipo_doc_new')->references('id')->on('tipos_documentos')->nullable();
		});


	}



	public function down()
	{
		Schema::drop('change_asked_assignment');
		Schema::drop('change_asked_data');
		Schema::drop('change_asked');
	}

}
