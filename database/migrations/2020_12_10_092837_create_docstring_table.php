<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/*

ALTER TABLE `matriculas` CHANGE `promovido` `promovido` VARCHAR(100) NOT NULL DEFAULT 'Automático';


ALTER TABLE `years` ADD `show_subasignaturas_en_finales` TINYINT(1) NOT NULL DEFAULT '1' AFTER `cant_asignatura_pierde_year`, ADD `mensaje_aprobo_con_pendientes` TINYINT(1) NOT NULL DEFAULT '1' AFTER `show_subasignaturas_en_finales`;



ALTER TABLE `years` ADD `show_materias_todas` TINYINT(1) NOT NULL DEFAULT '1' AFTER `mensaje_aprobo_con_pendientes`;


ALTER TABLE `users` ADD `profesor_id` INT(10) NULL DEFAULT NULL AFTER `periodo_id`;
*/


class CreateDocstringTable extends Migration {


	public function up()
	{

		Schema::table('profesores', function(Blueprint $table) {
			$table->string('num_doc', 255)->change();
		});


		Schema::table('years', function(Blueprint $table) {
			$table->boolean('show_subasignaturas_en_finales')->default(1);
			$table->boolean('mensaje_aprobo_con_pendientes')->default(1);
			$table->boolean('show_materias_todas')->default(1); // Ignorar el horario y mostrar todas las asignaturas
		});

		Schema::table('users', function(Blueprint $table) {
			$table->boolean('profesor_id')->unsigned()->nullable(); // Para asociar a un docente
		});


	}

	public function down()
	{

		Schema::table('profesores', function(Blueprint $table) {
			$table->integer('num_doc')->change();
		});

        Schema::table('years', function($table) {
            $table->dropColumn('show_subasignaturas_en_finales');
            $table->dropColumn('mensaje_aprobo_con_pendientes');
        });
	}

}
