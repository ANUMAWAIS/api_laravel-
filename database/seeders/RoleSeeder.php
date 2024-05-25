<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions with the 'api' guard
        Permission::firstOrCreate(['name' => 'read', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'update', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create', 'guard_name' => 'api']);
        
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'api')->get());

        $writerRole = Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'api']);
        $writerRole->syncPermissions(['update', 'read']);
}
}