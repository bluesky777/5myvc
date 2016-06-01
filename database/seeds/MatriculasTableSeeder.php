<?php


class MatriculasTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		DB::table('matriculas')->delete();

		Matricula::create([
			'id'			=> 1,
			'alumno_id'		=> 1,
			'grupo_id'		=> 1,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 2,
			'alumno_id'		=> 1,
			'grupo_id'		=> 4,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 3,
			'alumno_id'		=> 2,
			'grupo_id'		=> 1,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 4,
			'alumno_id'		=> 2,
			'grupo_id'		=> 4,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 5,
			'alumno_id'		=> 3,
			'grupo_id'		=> 2,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 6,
			'alumno_id'		=> 3,
			'grupo_id'		=> 5,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 7,
			'alumno_id'		=> 4,
			'grupo_id'		=> 2,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 8,
			'alumno_id'		=> 4,
			'grupo_id'		=> 6,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 9,
			'alumno_id'		=> 5,
			'grupo_id'		=> 2,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 10,
			'alumno_id'		=> 5,
			'grupo_id'		=> 7,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 11,
			'alumno_id'		=> 9,
			'grupo_id'		=> 1,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 12,
			'alumno_id'		=> 10,
			'grupo_id'		=> 2,
			'matriculado'	=> true,
		]);
		Matricula::create([
			'id'			=> 13,
			'alumno_id'		=> 20,
			'grupo_id'		=> 3,
			'matriculado'	=> true,
		]);

		$this->command->info("Algunos alumnos matriculados en algunos grupos.");
	}

}