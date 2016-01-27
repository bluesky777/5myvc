<?php namespace App\Http\Controllers;

class RolesController extends Controller {


	public function getIndex()
	{
		Eloquent::unguard();

		$roles = Role::with('perms')->get();
		return $roles;

	}
	public function getRolesconpermisos()
	{
		Eloquent::unguard();

		$roles = Role::allConPermisos();
		return $roles;

	}

	public function putAddpermission($id)
	{
		Eloquent::unguard();

		$rol = Role::find($id);
		$per = Permission::find(Input::get('permission_id'));

		$rol->attachPermission($per);

		return $per;

	}

	public function putAddroletouser($role_id)
	{
		Eloquent::unguard();

		$rol = Role::find($role_id);
		$user = User::find(Input::get('user_id'));

		if ($user->hasRole($rol->name)) {
			App::abort(400, 'Usuario ya tiene ese role.');
		}else{
			$user->attachRole($rol);
			$user->save();
		}
		

		return $user;

	}

	public function putRemoveroletouser($role_id)
	{

		$rol = Role::find($role_id);
		$user = User::find(Input::get('user_id'));

		if (!$user->hasRole($rol->name)) {
			App::abort(400, 'Usuario no tiene ese role para eliminar.');
		}else{
			$user->detachRole($rol);
			$user->save();
		}

		return $user;

	}

	public function putRemovepermission($id)
	{
		Eloquent::unguard();

		//$rol = Role::find($id)->permissions()->detach(Input::get('permission_id'));
		$res = DB::delete('delete from permission_role where permission_id = ? AND role_id = ? ', array(Input::get('permission_id'), $id));
		return $res;

	}


}