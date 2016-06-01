<?php


class VtVotacionesTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		$this->command->info('Borrando votaciones existentes en la tabla ...');
		DB::table('vt_votaciones')->delete();
		
		$votacion = VtVotacion::create([
			'id'			=>	1,
			'nombre'		=>	'Votaciones institucionales LAL 2015',
			'locked'		=>	false,
			'actual'		=>	true,
			'in_action'		=>	true,
			'fecha_inicio'	=>	date('A-m-d'),
			'fecha_fin'		=>	date('A-m-d'),
		]);

		$votacion = VtVotacion::create([
			'id'			=>	2,
			'nombre'		=>	'Votación estudiantes más amigable.',
			'locked'		=>	false,
			'actual'		=>	false,
			'in_action'		=>	false,
			'fecha_inicio'	=>	date('A-m-d'),
			'fecha_fin'		=>	date('A-m-d'),
		]);

		$this->command->info('Votaciones agregadas...');
		
	}

}