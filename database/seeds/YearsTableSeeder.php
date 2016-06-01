<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class YearsTableSeeder extends Seeder {

	public function run()
	{
		
		Eloquent::unguard();

		DB::table('years')->delete();
		
		Year::create([
			'id'				=> 1,
			'year'				=> 2015,
			'nombre_colegio'	=> 'LICEO ADVENTISTA LIBERTAD',
			'abrev_colegio'		=> 'LAL',
			#'ciudad_id'		=> 50000,
			'nota_minima_aceptada'	=> 70,
			'resolucion'		=> 'RESOLUCIÓN 2563 DE 2014',
			#'codigo_dane'		=> 'DF7658765',
			#'encabezado_certificado'	=> '',
			#'frase_final_certificado'	=> '',
			'actual'			=> true,
			'alumnos_can_see_notas' => true
		]);
		
		$this->command->info("Año 2014 y 2015 agregados.");





		/* OTRA FORMA ********************************************************************
		// Abrimos el archivo donde tengo los municipios restantes de Colombia
		// recorremos los registros y los ingresamos a la base de datos
		$this->command->info("Leemos los csv para las cuidades colombianas faltantes...");

		$csv = dirname(__FILE__) .'/SqlTables/CuidadesDeColombia.csv'; 
		$file_handle = fopen($csv, "r");

		while (!feof($file_handle)) {
		    $line = fgetcsv($file_handle);

		    if (empty($line)) {
		        continue; // skip blank lines
		    }

		    $c = array();
		    $c['nombre']		= $line[0];
		    $c['pais_codigo']	= $line[1];
		    $c['distrito']		= $line[2];
		    $c['poblacion']		= $line[3];

		    //$this->command->info( implode(",", $c));
		    DB::table('ciudads')->insert($c);
		}
		fclose($file_handle);
*/
	}

}