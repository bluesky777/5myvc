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
            $table->string('varicela')->nullable();
            $table->string('medicamento_diario')->nullable();
            
            $table->boolean('vac_influenza')->default(0);
            $table->boolean('vac_fiebre_amarilla')->default(0);
            $table->boolean('vac_tetano')->default(0);
            $table->boolean('vac_sarampion')->default(0);
            $table->boolean('vac_hepatitis_b')->default(0);
            $table->string('vac_otra')->nullable();
            
            $table->boolean('patol_asma')->default(0);
            $table->boolean('patol_bronquis')->default(0);
            $table->boolean('patol_diabetes')->default(0);
            $table->boolean('patol_anemia')->default(0);
            $table->boolean('patol_hipertension')->default(0);
            $table->boolean('patol_dermatitis')->default(0);
            $table->boolean('patol_depresion')->default(0);
            $table->string('patol_otro')->nullable();
            
            $table->boolean('fami_hipertension_arterial')->default(0);
            $table->boolean('fami_diabetes')->default(0);
            $table->boolean('fami_diabetes_mellitus')->default(0);
            $table->boolean('fami_cancer')->default(0);
            $table->boolean('fami_artritis')->default(0);
            $table->boolean('fami_hipotiroidismo')->default(0);
            $table->string('fami_otro')->nullable();
            
			$table->text('observaciones')->nullable(); 
			$table->integer('updated_by')->unsigned()->nullable();
			$table->timestamps();
		});
		Schema::table('antecedentes', function(Blueprint $table) {
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});





	}

	public function down()
	{
		Schema::drop('antecedentes');
	}

}
