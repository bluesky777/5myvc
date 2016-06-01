<?php


class PeriodosTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();
		// Borramos todos los periodos
		DB::table('periodos')->delete();

		Periodo::create([
			'id'			=> '1',
			'numero'		=> 1,
			'fecha_inicio'	=> '2015-01-20',
			'fecha_fin'		=> '2015-03-30',
			'actual'		=> true,
			'year_id'		=> 1,
			'fecha_plazo'	=> '2015-03-15',
		]);
	}

}