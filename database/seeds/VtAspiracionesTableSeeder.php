<?php


class VtAspiracionesTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		$this->command->info('Borrando aspiraciones existentes en la tabla ...');
		DB::table('vt_aspiraciones')->delete();
		
		$votacion = VtAspiracion::create([
			'id'			=>	1,
			'aspiracion'	=>	'Personero',
			'abrev'			=>	'Pers',
			'votacion_id'	=>	1

		]);
		$votacion = VtAspiracion::create([
			'id'			=>	2,
			'aspiracion'	=>	'Representante',
			'abrev'			=>	'Rep',
			'votacion_id'	=>	1

		]);

		$this->command->info('Aspiraciones agregadas');
		

	}

}