<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    // =====================
    // PERMISSIONS
    // =====================
    public function index()
    {
        $users       = User::with('roles', 'permissions')->get();
        $roles       = Role::all();
        $permissions = Permission::all();
        return view('admins.permissions', compact('users', 'roles', 'permissions'));
    }

    public function updateUser(Request $request, User $user)
    {
        $user->syncRoles($request->roles ?? []);
        $user->syncPermissions($request->permissions ?? []);
        return back()->with('success_message', 'Mise à jour effectuée.');
    }

    public function managePermissions()
    {
        $permissions = Permission::all();
        return view('admins.permissions.manage', compact('permissions'));
    }

    public function createPermission()
    {
        return view('admins.permissions.create');
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        Permission::create(['name' => $request->name, 'guard_name' => 'web']);
        return redirect()->route('permissions.manage')
            ->with('success_message', 'Permission ajoutée.');
    }

    public function editPermission(Permission $permission)
    {
        return view('admins.permissions.edit', compact('permission'));
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $request->name]);
        return redirect()->route('permissions.manage')
            ->with('success_message', 'Permission mise à jour.');
    }

    public function deletePermission(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions.manage')
            ->with('success_message', 'Permission supprimée.');
    }

    // =====================
    // ROLES
    // =====================
    public function manageRoles()
    {
        $roles       = Role::with('permissions')->get();
        $permissions = Permission::all();
        return view('admins.roles.manage', compact('roles', 'permissions'));
    }

    public function createRole()
    {
        $permissions = Permission::all();
        return view('admins.roles.create', compact('permissions'));
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->route('roles.manage')
            ->with('success_message', 'Rôle ajouté.');
    }

    public function editRole(Role $role)
    {
        $permissions = Permission::all();
        return view('admins.roles.edit', compact('role', 'permissions'));
    }

    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->route('roles.manage')
            ->with('success_message', 'Rôle mis à jour.');
    }

    public function deleteRole(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.manage')
            ->with('success_message', 'Rôle supprimé.');
    }
}