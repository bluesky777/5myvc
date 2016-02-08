<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('portafolio.index');
	
});
Route::get('/app', function()
{
	return View::make('dist.index');
});

Route::controller('login', 'LoginController');

Route::controller('alumnos', 'AlumnosController');
Route::resource('paises', 'PaisesController');
Route::controller('ciudades', 'CiudadesController');
Route::resource('tiposdocumento', 'TipoDocumentoController');
Route::controller('areas', 'AreasController');
Route::controller('materias', 'MateriasController');
Route::controller('asignaturas', 'AsignaturasController');
Route::controller('unidades', 'UnidadesController');
Route::controller('subunidades', 'SubunidadesController');
Route::resource('users', 'UsersController');
Route::controller('notas', 'NotasController');
Route::resource('estados_civiles', 'EstadosCivilesController');
Route::resource('niveles_educativos', 'NivelesEducativosController');
Route::controller('grados', 'GradosController');
Route::controller('grupos', 'GruposController');
Route::controller('matriculas', 'MatriculasController');
Route::controller('profesores', 'ProfesoresController');
Route::controller('contratos', 'ContratosController');
Route::controller('nota_comportamiento', 'NotaComportamientoController');
Route::controller('definiciones_comportamiento', 'DefinicionesComportamientoController');
Route::controller('frases', 'FrasesController');
Route::controller('myimages', 'ImagesController');
Route::controller('ChangesAsked', 'ChangeAskedController');
Route::controller('ausencias', 'AusenciasController');
Route::controller('parentescos', 'ParentescosController');
Route::controller('bitacoras', 'BitacorasController');
Route::controller('perfiles', 'PerfilesController');
Route::controller('roles', 'RolesController');
Route::controller('permissions', 'PermissionsController');
Route::controller('escalas', 'EscalasDeValoracionController');
Route::controller('frases_asignatura', 'FrasesAsignaturaController');
Route::controller('eventos', 'EventosController');
Route::controller('years', 'YearsController');
Route::controller('certificados', 'ConfigCertificadosController');
Route::controller('periodos', 'PeriodosController');
Route::controller('bolfinales', 'Informes\BolfinalesController');
Route::controller('puestos', 'Informes\PuestosAnualesController');

Route::controller('editnota', 'EditnotaController');

Route::controller('votaciones', 'VtVotacionesController');
Route::resource('aspiraciones', 'VtAspiracionesController');
Route::controller('participantes', 'VtParticipantesController');
Route::controller('candidatos', 'VtCandidatosController');
Route::controller('votos', 'VtVotosController');

Route::controller('planillas', 'PlanillasController');

Route::controller('certificados-estudio', 'CertificadosEstudioController');



Route::controller('password', 'RemindersController');


/*
Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
*/

