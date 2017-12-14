<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->string('username')->unique();
			$table->string('password', 64);
			$table->string('sexo', 1);
			$table->string('email')->nullable();
			$table->integer('imagen_id')->nullable();
			$table->boolean('is_superuser')->default(false);
			$table->string('tipo')->nullable(); // Alumno, Profesor, Acudiente, Usuario.
			$table->boolean('is_active')->default(true);
			$table->boolean('can_ask')->default(true); // Si es False, el usuario no podrá pedir cambios de imagen ni nada.
			$table->integer('periodo_id')->unsigned()->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->rememberToken();
			$table->timestamps();
		});
		Schema::table('users', function(Blueprint $table) {
			$table->foreign('periodo_id')->references('id')->on('periodos')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
