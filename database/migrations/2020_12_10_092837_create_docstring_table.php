<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocstringTable extends Migration {


	public function up()
	{

		Schema::table('profesores', function(Blueprint $table) {
			$table->string('num_doc', 255)->change();
		});

		
		Schema::table('years', function(Blueprint $table) {
			$table->boolean('show_subasignaturas_en_finales')->default(1);
			$table->boolean('mensaje_aprobo_con_pendientes')->default(1);
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
