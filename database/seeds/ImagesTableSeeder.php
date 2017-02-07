<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\ImageModel as Image;

class ImagesTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		$this->command->info('Borrando imágenes existentes en la tabla ...');
		DB::table('images')->delete();

		// Las imágenes de usuario por defecto.
		$img = Image::create(array(
			'id'		=> 1,
			'nombre'	=> 'default_male.jpg',
		));
		$img = Image::create(array(
			'id'		=> 2,
			'nombre'	=> 'default_female.jpg',
		));

		/*
		$cont = 2;
		foreach(range(1, 8) as $index)
		{
			$cont++;
			$img = Image::create(array(
				'id'		=> $cont,
				'nombre'	=> 'ejemplo/'.$index.'.jpg',
				'user_id'	=> $index,
			));

		}

		// Repetiré algunas imágenes para que pertenezcan al Admin
		$img = Image::create(array(
			'id'		=> 11,
			'nombre'	=> 'ejemplo/2.jpg',
			'user_id'	=> 1,
		));
		$img = Image::create(array(
			'id'		=> 12,
			'nombre'	=> 'ejemplo/3.jpg',
			'user_id'	=> 1,
		));
		$img = Image::create(array(
			'id'		=> 13,
			'nombre'	=> 'ejemplo/5.jpg',
			'user_id'	=> 1,
		));
		$img = Image::create(array(
			'id'		=> 14,
			'nombre'	=> 'ejemplo/6.jpg',
			'user_id'	=> 1,
		));
		$img = Image::create(array(
			'id'		=> 15,
			'nombre'	=> 'ejemplo/8.jpg',
			'user_id'	=> 1,
		));
		*/

		$this->command->info('Imágenes insertadas.');
		
		
	}

}