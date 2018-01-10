<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDefinitivasTable extends Migration {


	public function up()
	{

		Schema::create('df_grupos', function(Blueprint $table) {
            $table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('grupo_id')->unsigned()->nullable();
            $table->string('nombre');
            $table->string('abrev')->nullable();
            $table->integer('year_id')->unsigned();
            $table->integer('year')->unsigned()->nullable();
            $table->integer('titular_id')->unsigned()->nullable();
            $table->string('nombre_titular')->nullable();
            $table->string('nombre_img_titular')->nullable();
            $table->string('nombre_firma_titular')->nullable();
            $table->integer('puesto')->unsigned()->nullable();
            $table->decimal('puntaje', 7, 4)->nullable();
            $table->integer('grado_id')->unsigned();
            $table->integer('valormatricula')->nullable();
            $table->integer('valorpension')->nullable();
            $table->integer('orden')->nullable();
            $table->boolean('caritas')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
		Schema::table('df_grupos', function(Blueprint $table) {
			$table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('cascade');
			$table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
			$table->foreign('titular_id')->references('id')->on('profesores')->onDelete('cascade');
			$table->foreign('grado_id')->references('id')->on('grados')->onDelete('cascade');
		});



		Schema::create('df_alumnos', function(Blueprint $table) {
            $table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('alumno_id')->unsigned()->nullable();
            $table->string('no_matricula')->nullable();
            $table->string('nombres');
            $table->string('apellidos')->nullable();
            $table->string('sexo', 1);
			$table->integer('user_id')->unsigned()->nullable();
			$table->date('fecha_nac')->nullable();
			$table->string('ciudad_nac')->nullable();
			$table->string('tipo_doc')->nullable();
			$table->string('documento')->nullable();
			$table->string('ciudad_doc')->nullable();
			$table->string('tipo_sangre')->nullable();
			$table->string('eps')->nullable();
			$table->string('telefono')->nullable();
			$table->string('celular')->nullable();
			$table->string('direccion')->nullable();
			$table->string('barrio')->nullable();
			$table->integer('estrato')->nullable()->default(1);
			$table->string('ciudad_resid')->nullable(); // Cuidad de residencia
			$table->string('religion')->nullable();
			$table->string('email')->nullable();
			$table->string('facebook')->nullable(); // Si no asigna facebook, se tomará el email
            $table->integer('foto_id')->nullable();
            $table->string('nombre_foto')->nullable();
			$table->boolean('pazysalvo')->nullable()->default(true);
            $table->integer('deuda')->nullable()->default(0);
            $table->integer('year_id')->unsigned();
            $table->integer('year')->unsigned()->nullable();
            $table->integer('grupo_id')->unsigned()->nullable();
            $table->integer('grupo_id_df')->unsigned()->nullable();

            $table->integer('year_puesto')->unsigned()->nullable();
            $table->decimal('year_puntaje', 7, 4)->nullable();
            $table->decimal('year_comportamiento', 7, 4)->nullable();
            $table->string('year_comportamiento_desempenio')->nullable();
            $table->integer('year_tardanzas_instituc')->nullable();
            $table->integer('year_tardanzas_clases')->nullable();
            $table->integer('year_ausencias_instituc')->nullable();
            $table->integer('year_ausencias_clases')->nullable();

            $table->integer('per1_puesto')->unsigned()->nullable();
            $table->decimal('per1_puntaje', 7, 4)->nullable();
            $table->integer('per1_notas_perdidas')->unsigned()->nullable();
            $table->boolean('per1_recuperado')->default(false);
            $table->decimal('per1_comportamiento', 7, 4)->nullable();
            $table->string('per1_comportamiento_desempenio')->nullable();
            $table->integer('per1_tardanzas_instituc')->nullable();
            $table->integer('per1_tardanzas_clases')->nullable();
            $table->integer('per1_ausencias_instituc')->nullable();
            $table->integer('per1_ausencias_clases')->nullable();

            $table->integer('per2_puesto')->unsigned()->nullable();
            $table->decimal('per2_puntaje', 7, 4)->nullable();
            $table->integer('per2_notas_perdidas')->unsigned()->nullable();
            $table->boolean('per2_recuperado')->default(false);
            $table->decimal('per2_comportamiento', 7, 4)->nullable();
            $table->string('per2_comportamiento_desempenio')->nullable();
            $table->integer('per2_tardanzas_instituc')->nullable();
            $table->integer('per2_tardanzas_clases')->nullable();
            $table->integer('per2_ausencias_instituc')->nullable();
            $table->integer('per2_ausencias_clases')->nullable();

            $table->integer('per3_puesto')->unsigned()->nullable();
            $table->decimal('per3_puntaje', 7, 4)->nullable();
            $table->integer('per3_notas_perdidas')->unsigned()->nullable();
            $table->boolean('per3_recuperado')->default(false);
            $table->decimal('per3_comportamiento', 7, 4)->nullable();
            $table->string('per3_comportamiento_desempenio')->nullable();
            $table->integer('per3_tardanzas_instituc')->nullable();
            $table->integer('per3_tardanzas_clases')->nullable();
            $table->integer('per3_ausencias_instituc')->nullable();
            $table->integer('per3_ausencias_clases')->nullable();

            $table->integer('per4_puesto')->unsigned()->nullable();
            $table->decimal('per4_puntaje', 7, 4)->nullable();
            $table->integer('per4_notas_perdidas')->unsigned()->nullable();
            $table->boolean('per4_recuperado')->default(false);
            $table->decimal('per4_comportamiento', 7, 4)->nullable();
            $table->string('per4_comportamiento_desempenio')->nullable();
            $table->integer('per4_tardanzas_instituc')->nullable();
            $table->integer('per4_tardanzas_clases')->nullable();
            $table->integer('per4_ausencias_instituc')->nullable();
            $table->integer('per4_ausencias_clases')->nullable();

            $table->string('estado_matr', 4)->default('MATR'); // MATR, ASIS, RETI     == Matriculado, Asistente y Retirado
			$table->date('fecha_retiro')->nullable(); // Cuando fue retirado o desertado
			$table->date('fecha_matricula')->nullable(); // Cuando por fin lo matricularon
			$table->string('se_recomienda')->default('')->nullable(); // 'MATR CONDICIONAL', 'COMPR ACADEM', 'CAMBIO INSTITUCI'     == Lo que se recomienda 
			$table->string('entra_con')->default('')->nullable(); // 'MATR CONDICIONAL', 'COMPR ACADEM'     
			$table->string('created_by')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('df_alumnos', function(Blueprint $table) {
            $table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
            $table->foreign('grupo_id_df')->references('id')->on('df_grupos')->onDelete('cascade');
        });




		Schema::create('df_asignaturas', function(Blueprint $table) {
            $table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('alumno_id_df')->unsigned()->nullable();
            $table->integer('asignatura_id')->unsigned()->nullable();
            $table->integer('materia_id')->nullable();
            $table->string('materia_nombre');
            $table->string('materia_alias')->nullable();
			$table->integer('materia_orden')->nullable();
            $table->integer('area_id')->nullable();
			$table->string('area_nombre')->nullable();
			$table->integer('area_orden')->nullable();
            $table->integer('profesor_id')->nullable();
            $table->string('profesor_nombre')->nullable();
            $table->string('profesor_foto')->nullable();
            $table->string('profesor_firma')->nullable();
            $table->integer('creditos')->nullable();
            
            $table->decimal('year_definitiva', 7, 4)->nullable();
            $table->string('year_desempenio')->nullable();
            $table->integer('year_notas_perdidas')->unsigned()->nullable();
            $table->boolean('year_recuperada')->default(false);
            $table->integer('year_tardanzas_clases')->nullable();
            $table->integer('year_ausencias_clases')->nullable();
            
            $table->decimal('per1_definitiva', 7, 4)->nullable();
            $table->string('per1_desempenio')->nullable();
            $table->integer('per1_notas_perdidas')->unsigned()->nullable();
            $table->boolean('per1_recuperada')->default(false);
            $table->integer('per1_tardanzas_clases')->nullable();
            $table->integer('per1_ausencias_clases')->nullable();

            $table->decimal('per2_definitiva', 7, 4)->nullable();
            $table->string('per2_desempenio')->nullable();
            $table->integer('per2_notas_perdidas')->unsigned()->nullable();
            $table->boolean('per2_recuperada')->default(false);
            $table->integer('per2_tardanzas_clases')->nullable();
            $table->integer('per2_ausencias_clases')->nullable();

            $table->decimal('per3_definitiva', 7, 4)->nullable();
            $table->string('per3_desempenio')->nullable();
            $table->integer('per3_notas_perdidas')->unsigned()->nullable();
            $table->boolean('per3_recuperada')->default(false);
            $table->integer('per3_tardanzas_clases')->nullable();
            $table->integer('per3_ausencias_clases')->nullable();

            $table->decimal('per4_definitiva', 7, 4)->nullable();
            $table->string('per4_desempenio')->nullable();
            $table->integer('per4_notas_perdidas')->unsigned()->nullable();
            $table->boolean('per4_recuperada')->default(false);
            $table->integer('per4_tardanzas_clases')->nullable();
            $table->integer('per4_ausencias_clases')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
		Schema::table('df_asignaturas', function(Blueprint $table) {
			$table->foreign('alumno_id_df')->references('id')->on('df_alumnos')->onDelete('cascade');
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
		});



		Schema::create('df_unidades', function(Blueprint $table) {
            $table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('asignatura_id_df')->unsigned()->nullable();
            $table->integer('asignatura_id')->unsigned()->nullable();
            $table->string('definicion');
			$table->integer('porcentaje')->default(0)->nullable();
			$table->decimal('nota', 7, 4)->nullable();
			$table->integer('periodo_id')->unsigned();
			$table->integer('orden')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
		Schema::table('df_unidades', function(Blueprint $table) {
			$table->foreign('asignatura_id_df')->references('id')->on('df_asignaturas')->onDelete('cascade');
		});



		Schema::create('df_subunidades', function(Blueprint $table) {
            $table->engine = "InnoDB";
            $table->increments('id');
            $table->integer('unidad_id_df')->unsigned()->nullable();
            $table->integer('unidad_id')->unsigned()->nullable();
            $table->string('definicion');
			$table->integer('porcentaje')->default(0)->nullable();
			$table->decimal('nota', 7, 4)->nullable();
			$table->integer('periodo_id')->unsigned();
			$table->integer('asignatura_id')->unsigned();
			$table->integer('orden')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
		Schema::table('df_subunidades', function(Blueprint $table) {
			$table->foreign('unidad_id_df')->references('id')->on('df_unidades')->onDelete('cascade');
		});





	}

	public function down()
	{
		Schema::drop('df_subunidades');
		Schema::drop('df_unidades');
		Schema::drop('df_asignaturas');
		Schema::drop('df_alumnos');
		Schema::drop('df_grupos');
	}

}
