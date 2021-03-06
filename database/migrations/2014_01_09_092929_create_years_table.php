<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateYearsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('years', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('year');
			$table->string('nombre_colegio');
			$table->string('abrev_colegio')->nullable();
			$table->string('genero_colegio', 1)->default('F');
			$table->string('ciudad_id')->nullable();
			$table->integer('logo_id')->unsigned()->nullable();
			$table->integer('img_encabezado_id')->unsigned()->nullable();
			
			$table->integer('rector_id')->nullable(); // Código del profesor que es rector
			$table->integer('secretario_id')->nullable(); // Código del profesor que es secretario
			$table->integer('tesorero_id')->nullable(); // Código del profesor que es secretario
			$table->integer('coordinador_academico_id')->nullable(); // Código del profesor que es secretario
			$table->integer('coordinador_disciplinario_id')->nullable(); // Código del profesor que es secretario
			$table->integer('capellan_id')->nullable(); // Código del profesor que es secretario
			$table->integer('psicorientador_id')->nullable(); // Código del profesor que es secretario
			
			$table->string('nota_minima_aceptada', 3)->default(70);
			$table->integer('minu_hora_clase', 2)->default(50);
			$table->string('unidad_displayname')->default('Unidad');
			$table->string('unidades_displayname')->default('Unidades');
			$table->string('genero_unidad', 1)->default('F');
			$table->string('subunidad_displayname')->default('Subunidad');
			$table->string('subunidades_displayname')->default('Subunidades');
			$table->string('genero_subunidad', 1)->default('F');

			$table->text('resolucion')->nullable();
			$table->string('codigo_dane')->nullable();
			$table->string('caracter')->nullable()->default('Privado'); // Público, Privado
			$table->string('calendario')->nullable()->default('A'); // A B
			$table->string('jornada')->nullable()->default('Mañana y tarde'); // "Mañana y tarde", "Única"
			$table->text('encabezado_certificado')->nullable();
			$table->text('frase_final_certificado')->nullable();
			$table->boolean('actual')->default(0);
			$table->string('telefono')->nullable();
			$table->string('celular')->nullable();
			$table->string('website')->nullable();
			$table->string('website_myvc')->nullable();
			$table->boolean('alumnos_can_see_notas')->default(false);
			$table->boolean('profes_can_edit_alumnos')->default(false);
			$table->boolean('mostrar_puesto_boletin')->default(true);
			$table->boolean('mostrar_nota_comport_boletin')->default(true);
			$table->boolean('si_recupera_materia_recup_indicador')->default(true); // Si pasa la materia, ya no tiene por qué nivelar indicadores
			$table->boolean('year_pasado_en_bol')->default(true); // Mostrar una tabla con las notas perdidas del año pasado
			$table->boolean('show_fortaleza_bol')->default(true); // Mostrar Fortaleza o Debilida en Unidades en vez de el desempeño normal(Superior, Bajo...)

			$table->integer('config_certificado_estudio_id')->unsigned()->nullable();
			$table->integer('cant_areas_pierde_year')->nullable()->default(0); // Cantidad de áreas con las que se pierde un año
			$table->integer('cant_asignatura_pierde_year')->nullable()->default(0); // Cantidad de asignaturas con las que se pierde un año
			$table->string('contador_certificados')->default(''); 
			$table->string('contador_folios')->default(''); 
			$table->text('texto_acta_eval')->nullable(); 

			$table->boolean('prematr_antiguos')->default(false); // Si true, mostrará opción al padre para prematricularse
			$table->boolean('prematr_nuevos')->default(false);  // Si true, mostrará opción en la pantalla de login
			
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		
		// Configuración para crear los certificados.
		Schema::create('config_certificados', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');

			$table->string('nombre')->nullable();
			$table->integer('encabezado_img_id')->nullable();
			$table->integer('encabezado_width')->nullable()->default(20); // En centímetros
			$table->integer('encabezado_height')->nullable()->default(10); // En centímetros
			$table->integer('encabezado_margin_top')->nullable()->default(10); // En pixeles
			$table->integer('encabezado_margin_left')->nullable()->default(10); // En pixeles
			$table->boolean('encabezado_solo_primera_pagina')->nullable()->default(true);

			$table->integer('piepagina_img_id')->nullable();
			$table->integer('piepagina_width')->nullable()->default(20); // En centímetros
			$table->integer('piepagina_height')->nullable()->default(10); // En centímetros
			$table->integer('piepagina_margin_bottom')->nullable()->default(10); // En pixeles
			$table->integer('piepagina_margin_left')->nullable()->default(10); // En pixeles
			$table->boolean('piepagina_solo_ultima_pagina')->nullable()->default(true);

			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('updated_by')->unsigned()->nullable();
			$table->timestamps();
		});

		

		// Qué grupos se deben comparar para sacar los puestos.
		Schema::create('agrupacion_puestos', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('nombre')->nullable();
			$table->integer('imagen_id')->unsigned()->nullable();
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('updated_by')->unsigned()->nullable();
			$table->timestamps();
		});
		Schema::create('agrupacion_puestos_detalle', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('grupo_id')->unsigned()->nullable();
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('updated_by')->unsigned()->nullable();
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
		Schema::drop('years');
		Schema::drop('config_certificados');
		Schema::drop('agrupacion_puestos');
		Schema::drop('agrupacion_puestos_detalle');
	}

}
