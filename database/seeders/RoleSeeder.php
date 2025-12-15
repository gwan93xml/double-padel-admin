<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $permissions = [
            'read-member',
            'create-member',
            'update-member',
            'delete-member',
            'read-user',
            'create-user',
            'update-user',
            'delete-user',
            'read-role',
            'create-role',
            'update-role',
            'delete-role',
            'read-setting',
            'update-setting',
            'read-action',
            'create-action',
            'update-action',
            'delete-action',
            'read-dashboard',
            'read-module',
            'create-module',
            'update-module',
            'delete-module',
        ];
        foreach($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $role->givePermissionTo($permission);
        }
    }
}
