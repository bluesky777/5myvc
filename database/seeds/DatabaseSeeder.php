<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		// $this->call('UserTableSeeder');
		$this->call('YearsTableSeeder');
		$this->call('PeriodosTableSeeder');
		$this->call('TiposDocumentosTableSeeder');  // Listo!
		$this->call('PaisesTableSeeder');  // Listo!
		$this->call('UserTableSeeder');
		$this->call('RoleTableSeeder');
		//$this->call('ProfesoresTableSeeder');
		//$this->call('AlumnosTableSeeder');
		$this->call('ImagesTableSeeder'); // Insertar registros de las imágenes por default
		$this->call('NivelesEducativosTableSeeder');
		$this->call('GradosTableSeeder');
		$this->call('GruposTableSeeder');
		//$this->call('MatriculasTableSeeder');
		//$this->call('VtVotacionesTableSeeder');
		//$this->call('VtAspiracionesTableSeeder');
	}

}
