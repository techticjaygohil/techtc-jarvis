<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Jarvis\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    protected $toTruncate = ['model_has_permissions', 'model_has_roles', 'role_has_permissions', 'permissions', 'roles'];
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->toTruncate as $table) {
            DB::table($table)->truncate();
        }
        $data = array(
            [
                'name'       => 'Super Admin',
                'guard_name' => 'web',
            ],
            [
                'name'       => 'Admin',
                'guard_name' => 'web',
            ],
            [
                'name'       => 'Customer',
                'guard_name' => 'web',
            ],
        );
        Role::insert($data);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
