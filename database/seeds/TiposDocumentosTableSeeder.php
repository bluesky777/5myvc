<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\TipoDocumento;

class TiposDocumentosTableSeeder extends Seeder {

	public function run()
	{

		Eloquent::unguard();
		
		// Borramos todas las cuidades
		DB::table('tipos_documentos')->delete();

		TipoDocumento::create([
			'id' => '1',
			'tipo' => 'CÉDULA',
			'abrev' => 'CC',
		]);
		TipoDocumento::create([
			'tipo' => 'TARJETA DE IDENTIDAD',
			'id' => '2',
			'abrev' => 'TI',
		]);
		TipoDocumento::create([
			'id' => '3',
			'tipo' => 'NÚMERO ÚNICO DE IDENTIFICACIÓN PERSONAL',
			'abrev' => 'NUIP',
		]);
		TipoDocumento::create([
			'id' => '4',
			'tipo' => 'REGISTRO CIVIL',
			'abrev' => 'RC',
		]);
		TipoDocumento::create([
			'id' => '5',
			'tipo' => 'CÉDULA EXTRANJERA',
			'abrev' => 'CE',
		]);
		TipoDocumento::create([
			'id' => '6',
			'tipo' => 'PASAPORTE',
			'abrev' => 'PASP',
		]);
	}

}