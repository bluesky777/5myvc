<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



AdvancedRoute::controller('login', 'LoginController');

AdvancedRoute::controller('alumnos', 'AlumnosController');
AdvancedRoute::controller('importar', 'Alumnos\ImportarController');
AdvancedRoute::controller('acudientes', 'AcudientesController');
AdvancedRoute::controller('buscar', 'BuscarController');
AdvancedRoute::controller('paises', 'PaisesController');
AdvancedRoute::controller('ciudades', 'CiudadesController');
Route::resource('tiposdocumento', 'TipoDocumentoController');
AdvancedRoute::controller('areas', 'AreasController');
AdvancedRoute::controller('materias', 'MateriasController');
AdvancedRoute::controller('asignaturas', 'AsignaturasController');
AdvancedRoute::controller('unidades', 'UnidadesController');
AdvancedRoute::controller('subunidades', 'SubunidadesController');
AdvancedRoute::controller('users', 'UsersController');
AdvancedRoute::controller('notas', 'NotasController');
Route::resource('estados_civiles', 'EstadosCivilesController');
AdvancedRoute::controller('niveles_educativos', 'NivelesEducativosController');
AdvancedRoute::controller('grados', 'GradosController');
AdvancedRoute::controller('grupos', 'GruposController');
AdvancedRoute::controller('matriculas', 'MatriculasController');
AdvancedRoute::controller('cartera', 'CarteraController');
AdvancedRoute::controller('detalles', 'DetallesController');
AdvancedRoute::controller('profesores', 'ProfesoresController');
AdvancedRoute::controller('contratos', 'ContratosController');
AdvancedRoute::controller('nota_comportamiento', 'NotaComportamientoController');
AdvancedRoute::controller('definitivas_periodos', 'DefinitivasPeriodosController');
AdvancedRoute::controller('definiciones_comportamiento', 'DefinicionesComportamientoController');
AdvancedRoute::controller('frases', 'FrasesController');
AdvancedRoute::controller('ChangesAsked', 'ChangeAskedController');
AdvancedRoute::controller('ChangesAskedAssignment', 'ChangeAskedAssignmentController');
AdvancedRoute::controller('ausencias', 'AusenciasController');
AdvancedRoute::controller('parentescos', 'ParentescosController');
AdvancedRoute::controller('bitacoras', 'BitacorasController');
AdvancedRoute::controller('roles', 'RolesController');
AdvancedRoute::controller('permissions', 'PermissionsController');
AdvancedRoute::controller('escalas', 'EscalasDeValoracionController');
AdvancedRoute::controller('frases_asignatura', 'FrasesAsignaturaController');
AdvancedRoute::controller('eventos', 'EventosController');
AdvancedRoute::controller('years', 'YearsController');
AdvancedRoute::controller('certificados', 'ConfigCertificadosController');
AdvancedRoute::controller('periodos', 'PeriodosController');

# Informes
AdvancedRoute::controller('informes', 'Informes\InformesController');
AdvancedRoute::controller('bolfinales', 'Informes\BolfinalesController');
AdvancedRoute::controller('puestos', 'Informes\PuestosAnualesController');
AdvancedRoute::controller('planillas-ausencias', 'Informes\PlanillasAusenciasController');
AdvancedRoute::controller('notas-perdidas', 'Informes\NotasPerdidasController');
AdvancedRoute::controller('boletines', 'Informes\BoletinesController');
AdvancedRoute::controller('boletines2', 'Informes\Boletines2Controller');
AdvancedRoute::controller('simat', 'Informes\SimatController');
AdvancedRoute::controller('observador', 'Informes\ObservadorController');

# Pefiles
AdvancedRoute::controller('perfiles', 'Perfiles\PerfilesController');
AdvancedRoute::controller('myimages', 'Perfiles\ImagesController');
AdvancedRoute::controller('images-users', 'Perfiles\ImagesUsuariosController');

AdvancedRoute::controller('editnota', 'EditnotaController');

AdvancedRoute::controller('votaciones', 'VtVotacionesController');
AdvancedRoute::controller('aspiraciones', 'VtAspiracionesController');
AdvancedRoute::controller('participantes', 'VtParticipantesController');
AdvancedRoute::controller('candidatos', 'VtCandidatosController');
AdvancedRoute::controller('votos', 'VtVotosController');

AdvancedRoute::controller('planillas', 'PlanillasController');

AdvancedRoute::controller('certificados-estudio', 'CertificadosEstudioController');



AdvancedRoute::controller('password', 'RemindersController');


AdvancedRoute::controller('tardanzas/login', 'Tardanzas\TLoginController');
AdvancedRoute::controller('tardanzas/subir', 'Tardanzas\TSubirController');
AdvancedRoute::controller('actividades', 'Actividades\ActividadesController');
AdvancedRoute::controller('mis-actividades', 'Actividades\MisActividadesController');
AdvancedRoute::controller('preguntas', 'Actividades\PreguntasController');
AdvancedRoute::controller('opciones', 'Actividades\OpcionesController');
AdvancedRoute::controller('respuestas', 'Actividades\RespuestasController');
