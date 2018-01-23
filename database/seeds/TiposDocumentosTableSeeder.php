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
			'id' => '2',
			'tipo' => 'PERMISO ESPECIAL DE PERMANENCIA',
			'abrev' => 'PEP',
		]);
		TipoDocumento::create([
			'tipo' => 'TARJETA DE IDENTIDAD',
			'id' => '3',
			'abrev' => 'TI',
		]);
		TipoDocumento::create([
			'id' => '4',
			'tipo' => 'CÉDULA EXTRANJERA',
			'abrev' => 'CE',
		]);
		TipoDocumento::create([
			'id' => '5',
			'tipo' => 'REGISTRO CIVIL',
			'abrev' => 'RC',
		]);
		TipoDocumento::create([
			'id' => '6',
			'tipo' => 'NÚMERO DE IDENTIFICACIÓN PERSONAL',
			'abrev' => 'NIP',
		]);
		TipoDocumento::create([
			'id' => '7',
			'tipo' => 'NÚMERO ÚNICO DE IDENTIFICACIÓN PERSONAL',
			'abrev' => 'NUIP',
		]);
		TipoDocumento::create([
			'id' => '8',
			'tipo' => 'NÚMERO DE SECRETARÍA',
			'abrev' => 'NES',
		]);
		TipoDocumento::create([
			'id' => '9',
			'tipo' => 'CERTIFICADO...',
			'abrev' => 'CCB',
		]);
	}

}