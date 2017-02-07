<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Alumno;

class AlumnosTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		$this->command->info('Borrando alumnos existentes en la tabla ...');
		DB::table('alumnos')->delete();
		
		$faker = Faker::create();


		$this->command->info('Insertando nuevos alumnos a la tabla ...');

		$cont = 1;
		$imgCont = 0;
		foreach(range(1, 50) as $index)
		{
			$nombre = str_replace('.', '_', $faker->unique()->userName);
			$imgCont++;

			$user = User::create(array(
				'username'	=> $nombre,
				'password'	=> Hash::make('123'),
				'is_superuser'	=> false,
				'is_active'		=> true,
				//'imagen_id'		=> 'ejemplo/'.$imgCont . '.jpg',
			));
			$user->attachRole(3);
			$alum = array(
				'id'		=> $cont++,
				'no_matricula'	=> rand(10000, 9999999),
				'nombres'	=> $nombre,
				'apellidos'	=> $faker->lastName,
				'sexo'		=> rand(0, 1) ? 'M':'F',
				'fecha_nac'	=> $faker->date(),
				'email'		=> $faker->email(),
				'facebook'	=> $faker->email(),
				'celular'	=> $faker->phoneNumber,
				'direccion'	=> $faker->address,
				'religion'	=> rand(0, 1) ? 'Adventista':'Católico',
				'user_id'	=> $user->id,
			);

			//$this->command->info("	" . $index . ". -> " . implode(",", $alum));
			Alumno::create($alum);

			if ($imgCont==9) {
				$imgCont=0;
			}
		}
	}

}