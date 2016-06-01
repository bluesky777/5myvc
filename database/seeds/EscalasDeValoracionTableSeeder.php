<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class EscalasDeValoracionTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();
		
		$scala = new EscalaDeValoracion;
		$scala->desempenio	= 'SUPERIOR';
		$scala->valoracion	= 'E';
		$scala->porc_inicial= 91;
		$scala->porc_final	= 100;
		$scala->descripcion	= '';
		$scala->orden		= 5;
		$scala->perdido		= false;
		$scala->year_id		= 1;
		$scala->save();

		$scala = new EscalaDeValoracion;
		$scala->desempenio	= 'ALTO';
		$scala->valoracion	= 'S';
		$scala->porc_inicial= 81;
		$scala->porc_final	= 90;
		$scala->descripcion	= '';
		$scala->orden		= 4;
		$scala->perdido		= false;
		$scala->year_id		= 1;
		$scala->save();

		$scala = new EscalaDeValoracion;
		$scala->desempenio	= 'BÁSICO';
		$scala->valoracion	= 'A';
		$scala->porc_inicial= 70;
		$scala->porc_final	= 80;
		$scala->descripcion	= '';
		$scala->orden		= 3;
		$scala->perdido		= false;
		$scala->year_id		= 1;
		$scala->save();

		$scala = new EscalaDeValoracion;
		$scala->desempenio	= 'BAJO';
		$scala->valoracion	= 'I';
		$scala->porc_inicial= 40;
		$scala->porc_final	= 69;
		$scala->descripcion	= '';
		$scala->orden		= 2;
		$scala->perdido		= true;
		$scala->year_id		= 1;
		$scala->save();

		$scala = new EscalaDeValoracion;
		$scala->desempenio	= 'MUY BAJO';
		$scala->valoracion	= 'D';
		$scala->porc_inicial= 0;
		$scala->porc_final	= 39;
		$scala->descripcion	= '';
		$scala->orden		= 1;
		$scala->perdido		= true;
		$scala->year_id		= 1;
		$scala->save();



		$this->command->info('Escalas de valoración agregadas');
	}

}