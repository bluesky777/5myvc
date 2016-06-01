<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class ProfesoresTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		$this->command->info('Borrando profesores existentes en la tabla ...');
		DB::table('profesores')->delete();
		
		$faker = Faker::create();


		$this->command->info('Insertando nuevos profesores a la tabla ...');

		$cont = 1;
		foreach(range(1, 10) as $index)
		{
			$nombre = $faker->firstName;

			$user = User::create(array(
				'username'	=> $nombre,
				'password'	=> Hash::make('123'),
				'is_superuser'	=> false,
				'is_active'		=> true,
			));
			$user->attachRole(2);
			$prof = array(
				'id'		=> $cont++,
				'nombres'	=> $nombre,
				'apellidos'	=> $faker->lastName,
				'sexo'		=> rand(0, 1) ? 'M':'F',
				'fecha_nac'	=> $faker->date(),
				'email'		=> $faker->email(),
				'facebook'	=> $faker->email(),
				'celular'	=> $faker->phoneNumber,
				'direccion'	=> $faker->address,
				'user_id'	=> $user->id,
			);

			//$this->command->info("	" . $index . ". -> " . implode(",", $prof));
			Profesor::create($prof);
		}
	}

}