<?php


class NivelesEducativosTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		$this->command->info('Borrando niveles existentes en la tabla ...');
		DB::table('niveles_educativos')->delete();
		
		$nivel = array(
			'id'	=> 1,
			'nombre'=> 'Jardín Infantil / Educación Preescolar',
			'abrev'	=> 'Preescolar',
			'orden'	=> 1
		);
		NivelEducativo::create($nivel);

		$nivel = array(
			'id'	=> 2,
			'nombre'=> 'Escuela Primaria / Educación Básica Primaria',
			'abrev'	=> 'Primaria',
			'orden'	=> 2
		);
		NivelEducativo::create($nivel);

		$nivel = array(
			'id'	=> 3,
			'nombre'=> 'Básica / Educación Básica Secundaria',
			'abrev'	=> 'Secundaria',
			'orden'	=> 3
		);
		NivelEducativo::create($nivel);

		$nivel = array(
			'id'	=> 4,
			'nombre'=> 'Bachillerato / Educación Media',
			'abrev'	=> 'Media',
			'orden'	=> 4
		);
		NivelEducativo::create($nivel);

		$this->command->info('Niveles educativos agregados con éxito.');
		
	}

}