<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEnfermeriaTable extends Migration {


	public function up()
	{

		Schema::create('antecedentes', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('alumno_id')->unsigned();
            $table->string('cirugias')->nullable();
            $table->boolean('varicela')->nullable();
            $table->string('medicamento_diario')->nullable();
            
            $table->boolean('vac_influenza')->default(0);
            $table->boolean('vac_fiebre_amarilla')->default(0);
            $table->boolean('vac_tetano')->default(0);
            $table->boolean('vac_sarampion')->default(0);
            $table->boolean('vac_hepatitis_b')->default(0);
            $table->boolean('vac_otra')->nullable();
            $table->string('vac_cual')->nullable();
            
            $table->boolean('patol_asma')->default(0);
            $table->boolean('patol_bronquis')->default(0);
            $table->boolean('patol_diabetes')->default(0);
            $table->boolean('patol_anemia')->default(0);
            $table->boolean('patol_hipertension')->default(0);
            $table->boolean('patol_dermatitis')->default(0);
            $table->boolean('patol_depresion')->default(0);
            $table->boolean('patol_otro')->nullable();
            $table->string('patol_cual')->nullable();
            
            $table->boolean('fami_hipertension_arterial')->default(0);
            $table->boolean('fami_diabetes')->default(0);
            $table->boolean('fami_diabetes_mellitus')->default(0);
            $table->boolean('fami_cancer')->default(0);
            $table->boolean('fami_artritis')->default(0);
            $table->boolean('fami_hipotiroidismo')->default(0);
            $table->boolean('fami_otro')->nullable();
            $table->string('fami_cual')->nullable();
            
			$table->text('observaciones')->nullable(); 
			$table->integer('updated_by')->unsigned()->nullable();
			$table->timestamps();
		});
		Schema::table('antecedentes', function(Blueprint $table) {
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});



		Schema::create('registros_enfermeria', function(Blueprint $table) {
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('alumno_id')->unsigned();
            $table->dateTime('fecha_suceso')->nullable();
            $table->integer('signo_fc')->nullable(); // ppm frecuencia cardiaca
            $table->integer('signo_fr')->nullable(); // rpm Frecuencia respiratoria
            $table->decimal('signo_t', 4, 1)->nullable(); // grados C Temperatura
            $table->integer('signo_glu')->nullable(); // mg/dl -Glucometría - 
            $table->integer('signo_spo2')->nullable(); // SPO2 - Saturación de oxígeno - %
            $table->integer('signo_pa_dia')->nullable(); // Presión arterial - mmhg (120/80) diastólica
            $table->integer('signo_pa_sis')->nullable(); // Presión arterial - mmhg (120/80) sistólica
            $table->string('asignatura')->nullable();
            $table->string('motivo_consulta')->nullable();
            $table->text('descripcion_suceso')->nullable(); // Enfermedad actual / Lesión / Sintoma
            $table->text('tratamiento')->nullable(); // qué tratamiento se le realizó
			$table->text('observaciones')->nullable(); 
			$table->text('insumos_utilizados')->nullable(); 
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('updated_by')->unsigned()->nullable();
			$table->timestamps();
		});
		Schema::table('registros_enfermeria', function(Blueprint $table) {
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});





	}

	public function down()
	{
		Schema::drop('registros_enfermeria');
		Schema::drop('antecedentes');
	}

}
