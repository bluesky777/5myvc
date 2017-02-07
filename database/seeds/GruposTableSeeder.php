<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Grupo;

class GruposTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		DB::table('grupos')->delete();

		Grupo::create([
			'id'			=> 1,
			'nombre'		=> 'Prejardín',
			'abrev'			=> 'Prej',
			'year_id'		=> 1,
			'grado_id'		=> 1,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 1,
			'caritas'		=> true,
		]);
		Grupo::create([
			'id'			=> 2,
			'nombre'		=> 'Jardín',
			'abrev'			=> 'Jar',
			'year_id'		=> 1,
			'grado_id'		=> 2,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 2,
			'caritas'		=> true,
		]);
		Grupo::create([
			'id'			=> 3,
			'nombre'		=> 'Transición',
			'abrev'			=> 'Tra',
			'year_id'		=> 1,
			'grado_id'		=> 5,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 3,
			'caritas'		=> true,
		]);
		Grupo::create([
			'id'			=> 4,
			'nombre'		=> 'Primero',
			'abrev'			=> '1',
			'year_id'		=> 1,
			'grado_id'		=> 6,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 4,
			'caritas'		=> true,
		]);
		Grupo::create([
			'id'			=> 5,
			'nombre'		=> 'Segundo',
			'abrev'			=> '2',
			'year_id'		=> 1,
			'grado_id'		=> 7,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 5,
			'caritas'		=> true,
		]);
		Grupo::create([
			'id'			=> 6,
			'nombre'		=> 'Tercero',
			'abrev'			=> '3',
			'year_id'		=> 1,
			'grado_id'		=> 14,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 6,
			'caritas'		=> false,
		]);
		Grupo::create([
			'id'			=> 7,
			'nombre'		=> 'Cuarto',
			'abrev'			=> '4',
			'year_id'		=> 1,
			'grado_id'		=> 9,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 7,
			'caritas'		=> false,
		]);
		Grupo::create([
			'id'			=> 8,
			'nombre'		=> 'Quinto',
			'abrev'			=> '5',
			'year_id'		=> 1,
			'grado_id'		=> 10,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 8,
			'caritas'		=> false,
		]);
		Grupo::create([
			'id'			=> 9,
			'nombre'		=> 'Sexto',
			'abrev'			=> '6',
			'year_id'		=> 1,
			'grado_id'		=> 11,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 9,
			'caritas'		=> false,
		]);
		Grupo::create([
			'id'			=> 10,
			'nombre'		=> 'Séptimo',
			'abrev'			=> '7',
			'year_id'		=> 1,
			'grado_id'		=> 12,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 10,
			'caritas'		=> false,
		]);
		Grupo::create([
			'id'			=> 11,
			'nombre'		=> 'Octavo',
			'abrev'			=> '8',
			'year_id'		=> 1,
			'grado_id'		=> 13,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 11,
			'caritas'		=> false,
		]);
		Grupo::create([
			'id'			=> 12,
			'nombre'		=> 'Noveno',
			'abrev'			=> '9',
			'year_id'		=> 1,
			'grado_id'		=> 14,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 12,
			'caritas'		=> false,
		]);
		Grupo::create([
			'id'			=> 13,
			'nombre'		=> 'Décimo',
			'abrev'			=> '10',
			'year_id'		=> 1,
			'grado_id'		=> 15,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 13,
			'caritas'		=> false,
		]);
		Grupo::create([
			'id'			=> 14,
			'nombre'		=> 'Once',
			'abrev'			=> '11',
			'year_id'		=> 1,
			'grado_id'		=> 16,
			'valormatricula'=> 0,
			'valorpension'	=> 0,
			'orden'			=> 14,
			'caritas'		=> false,
		]);

		$this->command->info("Grupos agregados.");

	}

}