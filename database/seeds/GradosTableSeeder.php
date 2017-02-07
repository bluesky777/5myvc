<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Grado;

class GradosTableSeeder extends Seeder {

	public function run()
	{
		
		Eloquent::unguard();

		DB::table('grados')->delete();

		Grado::create([
			'id'		=> 1,
			'nombre'	=> 'Prejardín',
			'abrev'		=> 'Prej',
			'orden'		=> 1,
			'nivel_educativo_id' => 1
		]);
		Grado::create([
			'id'		=> 2,
			'nombre'	=> 'Jardín',
			'abrev'		=> 'Jar',
			'orden'		=> 2,
			'nivel_educativo_id' => 1
		]);
		Grado::create([
			'id'		=> 3,
			'nombre'	=> 'Preescolar',
			'abrev'		=> 'Pree',
			'orden'		=> 3,
			'nivel_educativo_id' => 1
		]);
		Grado::create([
			'id'		=> 4,
			'nombre'	=> 'Kínder',
			'abrev'		=> 'Kin',
			'orden'		=> 4,
			'nivel_educativo_id' => 1
		]);
		Grado::create([
			'id'		=> 5,
			'nombre'	=> 'Transición',
			'abrev'		=> 'Tra',
			'orden'		=> 5,
			'nivel_educativo_id' => 1
		]);


		Grado::create([
			'id'		=> 6,
			'nombre'	=> 'Primero',
			'abrev'		=> '1',
			'orden'		=> 6,
			'nivel_educativo_id' => 2
		]);
		Grado::create([
			'id'		=> 7,
			'nombre'	=> 'Segundo',
			'abrev'		=> '2',
			'orden'		=> 7,
			'nivel_educativo_id' => 2
		]);
		Grado::create([
			'id'		=> 8,
			'nombre'	=> 'Tercero',
			'abrev'		=> '3',
			'orden'		=> 8,
			'nivel_educativo_id' => 2
		]);
		Grado::create([
			'id'		=> 9,
			'nombre'	=> 'Cuarto',
			'abrev'		=> '4',
			'orden'		=> 9,
			'nivel_educativo_id' => 2
		]);
		Grado::create([
			'id'		=> 10,
			'nombre'	=> 'Quinto',
			'abrev'		=> '5',
			'orden'		=> 10,
			'nivel_educativo_id' => 2
		]);


		Grado::create([
			'id'		=> 11,
			'nombre'	=> 'Sexto',
			'abrev'		=> '6',
			'orden'		=> 11,
			'nivel_educativo_id' => 3
		]);
		Grado::create([
			'id'		=> 12,
			'nombre'	=> 'Séptimo',
			'abrev'		=> '7',
			'orden'		=> 12,
			'nivel_educativo_id' => 3
		]);
		Grado::create([
			'id'		=> 13,
			'nombre'	=> 'Octavo',
			'abrev'		=> '8',
			'orden'		=> 13,
			'nivel_educativo_id' => 3
		]);
		Grado::create([
			'id'		=> 14,
			'nombre'	=> 'Noveno',
			'abrev'		=> '9',
			'orden'		=> 14,
			'nivel_educativo_id' => 3
		]);


		Grado::create([
			'id'		=> 15,
			'nombre'	=> 'Décimo',
			'abrev'		=> '10',
			'orden'		=> 15,
			'nivel_educativo_id' => 4
		]);
		Grado::create([
			'id'		=> 16,
			'nombre'	=> 'Once',
			'abrev'		=> '11',
			'orden'		=> 16,
			'nivel_educativo_id' => 4
		]);


		$this->command->info('Grados agregados con éxito.');

	
	}

}