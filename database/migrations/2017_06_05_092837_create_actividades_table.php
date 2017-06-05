<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActividadesTable extends Migration {


	public function up()
	{
		// EVALUACIÓN -> Cuestionario ordenado de preguntas
		Schema::create('ws_actividades', function(Blueprint $table) {
			$table->engine = "InnoDB";
            $table->increments('id');
            $table->string('descripcion')->nullable();
            $table->integer('periodo_id')->unsigned()->nullable();
            $table->integer('asignatura_id')->unsigned();
            $table->boolean('compartida')->default(false)->nullable();
            $table->boolean('in_action')->default(false)->nullable(); // Para redireccionarlo de inmediato al loguearse
            $table->integer('duracion_preg')->nullable(); // En segundos. Duración de la pregunta si el examen es Dirigido y la pregunta no tiene duración asignada;
            $table->integer('duracion_exam')->nullable(); // En minutos. Duración del examen si es Independiente
            $table->boolean('one_by_one')->nullable(); // Se responde una pregunta a la vez o varias preguntas en una página
            $table->integer('puntaje_por_promedio')->nullable()->default(true); // Promediar en vez de sumar los puntos de cada pregunta.
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
			$table->softDeletes();
            $table->timestamps();
        });
		Schema::table('ws_actividades', function(Blueprint $table) {
			$table->foreign('periodo_id')->references('id')->on('periodos')->onDelete('cascade');
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
		});


		// Contenidos para preguntas
		Schema::create('ws_contenidos_preg', function(Blueprint $table) {
			$table->engine = "InnoDB";
            $table->increments('id');
            $table->text('definicion')->nullable();
            $table->integer('actividad_id')->unsigned(); 
            $table->boolean('is_cuadricula')->default(false)->nullable(); // Si true, se creará una cuadrícula de tipo encuesta.
            $table->integer('added_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
			$table->softDeletes();
            $table->timestamps();
        });
		Schema::table('ws_contenidos_preg', function(Blueprint $table) {
			$table->foreign('actividad_id')->references('id')->on('ws_actividades')->onDelete('cascade');
			$table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
		});


		// PREGUNTAS
		Schema::create('ws_preguntas', function(Blueprint $table) {
			$table->engine = "InnoDB";
            $table->increments('id');
            $table->text('enunciado')->nullable();
            $table->integer('actividad_id')->nullable()->unsigned(); // La pregunta puede pertenecer directamente a una activadad o a ...
            $table->integer('contenido_id')->nullable()->unsigned();  // ... un contenido. Uno será NULL
            $table->string('ayuda')->nullable();
            $table->string('tipo_pregunta')->nullable(); // Test, Multiple, Texto, Lista, Ordenar, Cuadrícula
            $table->integer('orden')->unsigned()->nullable();
            $table->integer('puntos')->unsigned()->nullable(); // Si el puntaje de la evaluación se saca por promedio, este valor no se toma.  
            $table->integer('duracion')->unsigned()->nullable(); // En segundos
            $table->boolean('aleatorias')->default(false)->nullable(); // Si true, las opciones no necesariamente saldrán como se hayan creado, sino de manera aleatoria.
            $table->string('texto_arriba')->nullable(); // Para el tipo de pregunta ORDENAR. Ej: Más viejos
            $table->string('texto_abajo')->nullable(); // Para el tipo de pregunta ORDENAR. Ej: Más nuevos
            $table->integer('added_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
			$table->softDeletes();
            $table->timestamps();
        });
		Schema::table('ws_preguntas', function(Blueprint $table) {
			$table->foreign('actividad_id')->references('id')->on('ws_actividades')->onDelete('cascade');
			$table->foreign('contenido_id')->references('id')->on('ws_contenidos_preg')->onDelete('cascade');
			$table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
		});


		// OPCIONES
		Schema::create('ws_opciones', function(Blueprint $table) {
			$table->engine = "InnoDB";
            $table->increments('id');
            $table->text('definicion')->nullable();
            $table->integer('pregunta_id')->unsigned();
            $table->integer('image_id')->unsigned()->nullable(); // si también quiere poner imagen
            $table->integer('orden')->unsigned()->nullable(); // Aparecerá como primera, segunda, etc. Solo importará si la pregunta NO está configurada para que sea de opciones aleatoria
            $table->boolean('is_correct')->nullable()->default(false); // Dice si esta opción es la correcta o no
            $table->timestamps();
        });
		Schema::table('ws_opciones', function(Blueprint $table) {
			$table->foreign('pregunta_id')->references('id')->on('ws_preguntas')->onDelete('cascade');
		});



		// OPCIONES CUADRÍCULA
		Schema::create('ws_opciones_cuadricula', function(Blueprint $table) {
			$table->engine = "InnoDB";
            $table->increments('id');
            $table->string('definicion')->nullable();
            $table->integer('contenido_id')->unsigned();
            $table->string('icono')->nullable(); // Para poner caritas si así lo desea.
            $table->timestamps();
        });
		Schema::table('ws_opciones_cuadricula', function(Blueprint $table) {
			$table->foreign('contenido_id')->references('id')->on('ws_contenidos_preg')->onDelete('cascade');
		});


		// ACTIVIDAD RESUELTA
		Schema::create('ws_actividades_resueltas', function(Blueprint $table) {
			$table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('alumno_id')->unsigned();
            $table->integer('actividad_id')->unsigned();
            $table->boolean('gran_final')->default(false); // Dice si el examen fue hecho en eliminatoria o como gran final.
            $table->boolean('terminado')->default(false); // Indica si finalizó la actividad
            $table->boolean('timeout')->default(true); // Se le acabó el tiempo??
            $table->integer('deleted_by')->unsigned()->nullable();
			$table->softDeletes();
            $table->timestamps();
        });
		Schema::table('ws_actividades_resueltas', function(Blueprint $table) {
			$table->foreign('alumno_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('actividad_id')->references('id')->on('ws_actividades')->onDelete('cascade');
		});


		Schema::create('ws_actividades_compartidas', function(Blueprint $table) {
			$table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('actividad_id')->unsigned()->nullable();
            $table->integer('grupo_id')->unsigned()->nullable();
            $table->boolean('para_profesores')->default(false);
            $table->boolean('para_acudientes')->default(false);
            $table->timestamps();
        });
		Schema::table('ws_actividades_compartidas', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->foreign('actividad_id')->references('id')->on('ws_actividades')->onDelete('cascade');
			$table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('cascade');
		});


		/* 
		Respuesta de un usuario a una pregunta de un Test.
		*/
		Schema::create('ws_respuestas', function(Blueprint $table) {
			$table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('actividad_resuelta_id')->unsigned()->nullable();

            $table->integer('pregunta_id')->unsigned()->nullable();
            $table->integer('tiempo')->unsigned()->nullable(); // Segundos
            $table->string('tipo_pregunta')->nullable(); // Test, Multiple, Texto, Lista, Ordenar. Es redundante.
            $table->integer('opcion_id')->unsigned()->nullable();
			$table->integer('opcion_cuadricula_id')->unsigned()->nullable();

            $table->timestamps();
        });
		Schema::table('ws_respuestas', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->foreign('actividad_resuelta_id')->references('id')->on('ws_actividades_resueltas')->onDelete('cascade');
			$table->foreign('pregunta_id')->references('id')->on('ws_preguntas')->onDelete('cascade');
			$table->foreign('opcion_id')->references('id')->on('ws_opciones')->onDelete('cascade');
			$table->foreign('opcion_cuadricula_id')->references('id')->on('ws_opciones_cuadricula')->onDelete('cascade');
		});

	}

	public function down()
	{
		Schema::drop('ws_respuestas');
		Schema::drop('ws_actividades_resueltas');

		Schema::drop('ws_opciones_cuadricula');
		Schema::drop('ws_opciones');
		Schema::drop('ws_preguntas');
		Schema::drop('ws_contenidos_preg');
		Schema::drop('ws_actividades');
	}

}
