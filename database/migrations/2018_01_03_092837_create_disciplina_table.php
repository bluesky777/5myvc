<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDisciplinaTable extends Migration {


	public function up()
	{

		Schema::create('dis_configuraciones', function(Blueprint $table) {
      		$table->engine = "InnoDB";
                  $table->increments('id');
                  $table->integer('year_id')->unsigned()->nullable();
                  $table->boolean('reinicia_por_periodo')->default(false)->nullable(); // Si es false, las faltas_tipo_1 se acumularán de periodo en periodo.
                  
                  $table->string('falta_tipo1_displayname')->default('Falta tipo 1'); // Cómo se llamarán? puede ser "Situación tipo 1"
                  $table->string('faltas_tipo1_displayname')->default('Faltas tipo 1');
                  $table->string('genero_falta_t1', 1)->default('F');
                  $table->string('falta_tipo2_displayname')->default('Falta tipo 2'); 
                  $table->string('faltas_tipo2_displayname')->default('Faltas tipo 2');
                  $table->string('genero_falta_t2', 1)->default('F');
                  $table->string('falta_tipo3_displayname')->default('Falta tipo 3'); 
                  $table->string('faltas_tipo3_displayname')->default('Faltas tipo 3');
                  $table->string('genero_falta_t3', 1)->default('F');

                  $table->integer('cant_tard_to_ft1')->default(5); // Cantidad de tardanzas en la entrada que equivalen a una Falta tipo 1. Si es cero, no se convertirán automáticamente
                  $table->integer('cant_ft1_to_ft2')->default(3); // Cantidad de Faltas tipo 1 que se convertirán en Faltas tipo 2
                  $table->integer('cant_ft2_to_ft3')->default(3); // Cantidad de Faltas tipo 1 que se convertirán en Faltas tipo 2
                  
                  $table->text('definicion_ft1')->nullable(); 
                  $table->text('definicion_ft2')->nullable(); 
                  $table->text('definicion_ft3')->nullable(); 
                  $table->softDeletes();
                  $table->timestamps();
            });
		Schema::table('dis_configuraciones', function(Blueprint $table) {
			$table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
		});


		Schema::create('dis_ordinales', function(Blueprint $table) {
			$table->engine = "InnoDB";
                  $table->increments('id');
                  $table->integer('year_id')->unsigned();
                  $table->integer('tipo')->unsigned()->nullable(); // Tipo 1, 2 o 3
                  $table->string('ordinal')->nullable(); // Artículo en el manual de convivencia
                  $table->text('descripcion')->nullable();
                  
                  $table->integer('updated_by')->unsigned()->nullable();
                  $table->integer('deleted_by')->unsigned()->nullable();
      		$table->softDeletes();
                  $table->timestamps();
            });
		Schema::table('dis_ordinales', function(Blueprint $table) {
			$table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
		});



		Schema::create('dis_procesos', function(Blueprint $table) {
			$table->engine = "InnoDB";
                  $table->increments('id');
                  $table->text('descripcion')->nullable();
                  $table->integer('alumno_id')->unsigned()->nullable(); 
                  $table->integer('year_id')->unsigned()->nullable(); 
                  $table->integer('periodo_id')->unsigned()->nullable(); 
                  $table->integer('profesor_id')->unsigned()->nullable(); 
                  $table->dateTime('fecha_hora_aprox')->nullable(); // Fecha y hora aproximada del incidente 
                  $table->integer('ordinal_id')->unsigned()->nullable();
                  $table->integer('asignatura_id')->unsigned()->nullable();
                  $table->string('testigos')->nullable(); 
                  $table->string('descargo')->nullable(); 
                  
                  $table->integer('added_by')->unsigned()->nullable();
                  $table->integer('deleted_by')->unsigned()->nullable();
      		$table->softDeletes();
                  $table->timestamps();
            });
		Schema::table('dis_procesos', function(Blueprint $table) {
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
			$table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
			$table->foreign('periodo_id')->references('id')->on('periodos')->onDelete('cascade');
			$table->foreign('profesor_id')->references('id')->on('profesores')->onDelete('cascade');
			$table->foreign('ordinal_id')->references('id')->on('dis_ordinales')->onDelete('cascade');
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
			$table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
        });
        


		Schema::create('dis_acciones_restaurativas', function(Blueprint $table) {
			$table->engine = "InnoDB";
                  $table->increments('id');
                  $table->integer('ocasionada_por_proceso_id')->unsigned()->nullable(); // Código del Proceso que obligó a crear esta acción restaurativa
                  $table->date('fecha_colocacion')->nullable(); // Fecha en que se decidió que el alumno haría esta acción
                  $table->date('fecha_plazo')->nullable(); // Fecha en que el alumno ya debió haber realizado la acción
                  $table->boolean('cumplida')->nullable(); // Si True, el alumno ya cumplió con la acción
                  
                  $table->integer('created_by')->unsigned()->nullable();
                  $table->integer('updated_by')->unsigned()->nullable();
                  $table->integer('deleted_by')->unsigned()->nullable();
      		$table->softDeletes();
                  $table->timestamps();
            });
		Schema::table('dis_acciones_restaurativas', function(Blueprint $table) {
			$table->foreign('ocasionada_por_proceso_id')->references('id')->on('dis_procesos')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
		});





	}

	public function down()
	{
            Schema::drop('dis_acciones_restaurativas');
		Schema::drop('dis_procesos');
		Schema::drop('dis_ordinales');
		Schema::drop('dis_configuraciones');
	}

}
