<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    // ── PAGE PRINCIPALE : permissions par rôle ────────────────

    public function index()
    {
        $roles       = Role::with('permissions')->get();
        $permissions = Permission::all();

        $categories = $this->categories();

        return view('admins.permissions', compact('roles', 'permissions', 'categories'));
    }

    public function updateRole(Request $request, Role $role)
    {
        $role->syncPermissions($request->permissions ?? []);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success_message', 'Permissions du rôle mises à jour.');
    }

    // ── GESTION MEMBRES ───────────────────────────────────────

    public function manageRoles()
    {
        return redirect()->route('administrateurs.index');
    }

    // ── CRUD PERMISSIONS ──────────────────────────────────────

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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('permissions.manage')
            ->with('success_message', 'Permission mise à jour.');
    }

    public function deletePermission(Permission $permission)
    {
        $permission->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('permissions.manage')
            ->with('success_message', 'Permission supprimée.');
    }

    public function createRole()
    {
        return redirect()->route('administrateurs.create');
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $role->syncPermissions($request->permissions ?? []);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('administrateurs.index')
            ->with('success_message', 'Rôle ajouté.');
    }

    public function editRole(Role $role)
    {
        return redirect()->route('administrateurs.index');
    }

    public function deleteRole(Role $role)
    {
        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('administrateurs.index')
            ->with('success_message', 'Rôle supprimé.');
    }

    // ── HELPER ────────────────────────────────────────────────

    private function categories(): array
    {
        return [
            'Employers'      => ['view employers', 'create employer', 'edit employer', 'delete employer'],
            'Contrats'       => ['view contracts', 'edit contract', 'delete contract', 'download contract pdf'],
            'Paiements'      => ['view payments', 'process payments', 'download invoice'],
            'Congés'         => ['view leaves', 'create leave', 'edit leave', 'approve leave', 'reject leave'],
            'Départements'   => ['view departments', 'create department', 'edit department', 'delete department'],
            'Rôles'          => ['view roles', 'create role', 'delete role'],
            'Configurations' => ['view settings', 'edit settings'],
        ];
    }
}