<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;


/*
CREATE TABLE `uniformes` (
  `id` int(10) UNSIGNED NOT NULL,
  `asignatura_id` int(10) UNSIGNED DEFAULT NULL,
  `materia` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL, -- el nombre
  `alumno_id` int(10) UNSIGNED DEFAULT NULL,
  `periodo_id` int(10) UNSIGNED DEFAULT NULL,
  `contrario` tinyint(1) NOT NULL DEFAULT 0, -- uniforme del día equicocado
  `sin_uniforme` tinyint(1) NOT NULL DEFAULT 0, -- va de particular
  `incompleto` tinyint(1) NOT NULL DEFAULT 0,
  `cabello` tinyint(1) NOT NULL DEFAULT 0,
  `accesorios` tinyint(1) NOT NULL DEFAULT 0,
  `otro1` tinyint(1) NOT NULL DEFAULT 0, -- otra opción definida en Year
  `otro2` tinyint(1) NOT NULL DEFAULT 0, -- otra opción definida en Year
  `excusado` tinyint(1) NOT NULL DEFAULT 0,
  `descripcion` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `uploaded` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Indices de la tabla `uniformes`
--
ALTER TABLE `uniformes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uniformes_asignatura_id_foreign` (`asignatura_id`),
  ADD KEY `uniformes_alumno_id_foreign` (`alumno_id`),
  ADD KEY `uniformes_periodo_id_foreign` (`periodo_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `uniformes`
--
ALTER TABLE `uniformes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `uniformes`
--
ALTER TABLE `uniformes`
  ADD CONSTRAINT `uniformes_alumno_id_foreign` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `uniformes_asignatura_id_foreign` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `uniformes_periodo_id_foreign` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE;
COMMIT;

 */

class CreateUniformesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('uniformes', function(Blueprint $table)
		{
			$table->engine = "InnoDB";
			$table->increments('id');
			$table->integer('asignatura_id')->unsigned()->nullable();
			$table->string('materia')->nullable(); // el nombre
			$table->integer('alumno_id')->unsigned()->nullable();
			$table->integer('periodo_id')->unsigned()->nullable();
			$table->boolean('contrario')->default(false); // uniforme del día equicocado
			$table->boolean('sin_uniforme')->default(false); // va de particular
			$table->boolean('incompleto')->default(false);
			$table->boolean('cabello')->default(false);
			$table->boolean('accesorios')->default(false);
			$table->boolean('otro1')->default(false); // otra opción definida en Year
			$table->boolean('otro2')->default(false);
			$table->boolean('excusado')->default(false);
			$table->text('descripcion')->nullable(); // Detalles, descargo
			$table->dateTime('fecha_hora')->nullable();
			$table->string('uploaded', 20)->nullable();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->integer('deleted_by')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::table('uniformes', function(Blueprint $table) {
			$table->foreign('asignatura_id')->references('id')->on('asignaturas')->onDelete('cascade');
			$table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
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
		Schema::drop('uniformes');
	}

}
