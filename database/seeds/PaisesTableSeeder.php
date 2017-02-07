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
		$this->command->info("Dos paises ingresados.");
	}

}