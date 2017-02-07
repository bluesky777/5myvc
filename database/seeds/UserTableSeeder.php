<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		User::create(array(
			'id'		=> 1,
			'username'	=> 'admin',
			'email'		=> 'davidguerrero777@gmail.com',
			'password'	=> Hash::make('123'),
			'sexo'		=> 'M',
			'tipo'		=> 'Usuario',
			'is_superuser'	=> true,
			'is_active'		=> true,
		));
	}

}