<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RoleTableSeeder extends Seeder {

	public function run()
	{
		Eloquent::unguard();

		$this->command->info('Borrando roles y permisos...');
		DB::table('role_user')->delete();
		DB::table('permission_role')->delete();
		DB::table('roles')->delete();
		DB::table('permissions')->delete();
		
		$Admin = new Role();
		$Admin->id = 1;
		$Admin->name = 'Admin';
		$Admin->save();

		$userAdmin = User::where('username', '=', 'admin')->get()[0];
		$userAdmin->attachRole($Admin);

		$Profesor = new Role();
		$Profesor->id = 2;
		$Profesor->name = 'Profesor';
		$Profesor->save();

		$Alumno = new Role();
		$Alumno->id = 3;
		$Alumno->name = 'Alumno';
		$Alumno->save();

		$Acudiente = new Role();
		$Acudiente->id = 4;
		$Acudiente->name = 'Acudiente';
		$Acudiente->save();

		$Manager = new Role();
		$Manager->id = 5;
		$Manager->name = 'Manager';
		$Manager->save();

		$Asistente = new Role();
		$Asistente->id = 6;
		$Asistente->name = 'Asistente';
		$Asistente->save();

		$Asistente = new Role();
		$Asistente->id = 7;
		$Asistente->name = 'Enfermero';
		$Asistente->save();

		$Asistente = new Role();
		$Asistente->id = 8;
		$Asistente->name = 'Coord disciplinario';
		$Asistente->save();

		$Asistente = new Role();
		$Asistente->id = 9;
		$Asistente->name = 'Coord académico';
		$Asistente->save();
		
		$Asistente = new Role();
		$Asistente->id = 10;
		$Asistente->name = 'Rector';
		$Asistente->save();

		$Asistente = new Role();
		$Asistente->id = 11;
		$Asistente->name = 'Psicólogo';
		$Asistente->save();




		$perm_teacher = new Permission();
		$perm_teacher->id = 1;
		$perm_teacher->name = 'can_work_like_teacher';
		$perm_teacher->display_name = 'Puede ver actuar como profesor';
		$perm_teacher->description = 'Interactúa en la plataforma como un profesor';
		$perm_teacher->save();

		$perm_student = new Permission();
		$perm_student->id = 2;
		$perm_student->name = 'can_work_like_student';
		$perm_student->display_name = 'Puede ver actuar como estudiante';
		$perm_student->description = 'Interactúa en la plataforma como un estudiante';
		$perm_student->save();

		$perm_acud = new Permission();
		$perm_acud->id = 3;
		$perm_acud->name = 'can_work_like_acudiente';
		$perm_acud->display_name = 'Puede ver actuar como acudiente';
		$perm_acud->description = 'Interactúa en la plataforma como un acudiente';
		$perm_acud->save();

		$accept_img = new Permission();
		$accept_img->id = 4;
		$accept_img->name = 'can_accept_images';
		$accept_img->display_name = 'Puede aceptar o rechazar imágenes';
		$accept_img->description = 'Puede aceptar o rechazar imágenes propuestas para foto oficial o imagen de usuario';
		$accept_img->save();

		$can_edit_alumnos = new Permission();
		$can_edit_alumnos->id = 5;
		$can_edit_alumnos->name = 'can_edit_alumnos';
		$can_edit_alumnos->display_name = 'can_edit_alumnos';
		$can_edit_alumnos->description = 'can_edit_alumnos';
		$can_edit_alumnos->save();

		$can_edit_usuarios = new Permission();
		$can_edit_usuarios->id = 6;
		$can_edit_usuarios->name = 'can_edit_usuarios';
		$can_edit_usuarios->display_name = 'can_edit_usuarios';
		$can_edit_usuarios->description = 'can_edit_usuarios';
		$can_edit_usuarios->save();

		$can_edit_notas = new Permission();
		$can_edit_notas->id = 7;
		$can_edit_notas->name = 'can_edit_notas';
		$can_edit_notas->display_name = 'can_edit_notas';
		$can_edit_notas->description = 'can_edit_notas';
		$can_edit_notas->save();

		$can_edit_years = new Permission();
		$can_edit_years->id = 8;
		$can_edit_years->name = 'can_edit_years';
		$can_edit_years->display_name = 'can_edit_years';
		$can_edit_years->description = 'can_edit_years';
		$can_edit_years->save();

		$can_edit_periodos = new Permission();
		$can_edit_periodos->id = 9;
		$can_edit_periodos->name = 'can_edit_periodos';
		$can_edit_periodos->display_name = 'can_edit_periodos';
		$can_edit_periodos->description = 'can_edit_periodos';
		$can_edit_periodos->save();

		$can_edit_paises = new Permission();
		$can_edit_paises->id = 10;
		$can_edit_paises->name = 'can_edit_paises';
		$can_edit_paises->display_name = 'can_edit_paises';
		$can_edit_paises->description = 'can_edit_paises';
		$can_edit_paises->save();

		$can_edit_ciudades = new Permission();
		$can_edit_ciudades->id = 11;
		$can_edit_ciudades->name = 'can_edit_ciudades';
		$can_edit_ciudades->display_name = 'can_edit_ciudades';
		$can_edit_ciudades->description = 'can_edit_ciudades';
		$can_edit_ciudades->save();

		$can_edit_disciplinas = new Permission();
		$can_edit_disciplinas->id = 12;
		$can_edit_disciplinas->name = 'can_edit_disciplinas';
		$can_edit_disciplinas->display_name = 'can_edit_disciplinas';
		$can_edit_disciplinas->description = 'can_edit_disciplinas';
		$can_edit_disciplinas->save();

		$can_edit_profesores = new Permission();
		$can_edit_profesores->id = 13;
		$can_edit_profesores->name = 'can_edit_profesores';
		$can_edit_profesores->display_name = 'can_edit_profesores';
		$can_edit_profesores->description = 'can_edit_profesores';
		$can_edit_profesores->save();

		$can_edit_eventos = new Permission();
		$can_edit_eventos->id = 14;
		$can_edit_eventos->name = 'can_edit_eventos';
		$can_edit_eventos->display_name = 'can_edit_eventos';
		$can_edit_eventos->description = 'can_edit_eventos';
		$can_edit_eventos->save();

		$can_edit_votaciones = new Permission();
		$can_edit_votaciones->id = 15;
		$can_edit_votaciones->name = 'can_edit_votaciones';
		$can_edit_votaciones->display_name = 'can_edit_votaciones';
		$can_edit_votaciones->description = 'can_edit_votaciones';
		$can_edit_votaciones->save();

		$can_edit_aspiraciones = new Permission();
		$can_edit_aspiraciones->id = 16;
		$can_edit_aspiraciones->name = 'can_edit_aspiraciones';
		$can_edit_aspiraciones->display_name = 'can_edit_aspiraciones';
		$can_edit_aspiraciones->description = 'can_edit_aspiraciones';
		$can_edit_aspiraciones->save();

		$can_edit_participantes = new Permission();
		$can_edit_participantes->id = 17;
		$can_edit_participantes->name = 'can_edit_participantes';
		$can_edit_participantes->display_name = 'can_edit_participantes';
		$can_edit_participantes->description = 'can_edit_participantes';
		$can_edit_participantes->save();

		$can_edit_candidatos = new Permission();
		$can_edit_candidatos->id = 18;
		$can_edit_candidatos->name = 'can_edit_candidatos';
		$can_edit_candidatos->display_name = 'can_edit_candidatos';
		$can_edit_candidatos->description = 'can_edit_candidatos';
		$can_edit_candidatos->save();


		$can_edit_unidades = new Permission();
		$can_edit_unidades->id = 19;
		$can_edit_unidades->name = 'can_edit_unidades_subunidades';
		$can_edit_unidades->display_name = 'can_edit_unidades_subunidades';
		$can_edit_unidades->description = 'can_edit_unidades_subunidades';
		$can_edit_unidades->save();


		$Profesor->attachPermission($perm_teacher);
		$Alumno->attachPermission($perm_student);
		$Acudiente->attachPermission($perm_acud);

		$Manager->attachPermission($accept_img);
		$Manager->attachPermission($can_edit_alumnos);
		$Manager->attachPermission($can_edit_usuarios);
		$Manager->attachPermission($can_edit_notas);
		$Manager->attachPermission($can_edit_years);
		$Manager->attachPermission($can_edit_periodos);
		$Manager->attachPermission($can_edit_paises);
		$Manager->attachPermission($can_edit_ciudades);
		$Manager->attachPermission($can_edit_disciplinas);
		$Manager->attachPermission($can_edit_profesores);
		$Manager->attachPermission($can_edit_eventos);
		$Manager->attachPermission($can_edit_votaciones);
		$Manager->attachPermission($can_edit_aspiraciones);
		$Manager->attachPermission($can_edit_participantes);
		$Manager->attachPermission($can_edit_candidatos);
		$Manager->attachPermission($can_edit_unidades);

		//$Asistente->attachPermission($see_panel);

		/*
		$Alumno->attachPermission($print);

		$user1 = User::find(1);
		$user2 = User::find(2);

		$user1->attachRole($Profesor);
		$user2->attachRole($Alumno);
		*/
	}

}