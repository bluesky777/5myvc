<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pais;

class PaisesTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();
		// Borramos todos los paises
		DB::table('paises')->delete();

		Pais::create([
			'id' => '1',
			'pais' => 'COLOMBIA',
			'abrev' => 'CO'
		]);
		Pais::create([
			'id' => '2',
			'pais' => 'VENEZUELA',
			'abrev' => 'VE'
		]);
		Pais::create([
			'id' => '3',
			'pais' => 'ECUADOR',
			'abrev' => 'EC'
		]);
		Pais::create([
			'id' => '4',
			'pais' => 'PERÚ',
			'abrev' => 'PE'
		]);
		Pais::create([
			'id' => '5',
			'pais' => 'PANAMÁ',
			'abrev' => 'PA'
		]);
		Pais::create([
			'id' => '6',
			'pais' => 'COSTA RICA',
			'abrev' => 'CR'
		]);
		$this->command->info("Dos paises ingresados.");
	}

}