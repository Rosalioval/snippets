<?php

use Illuminate\Database\Seeder;

class LuUserRolesSeeder extends Seeder
{
    const TABLE = 'lu_user_roles';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(self::TABLE)->insert([
            'role' => 'super-admin',
        ]);
        DB::table(self::TABLE)->insert([
            'role' => 'admin',
        ]);
        DB::table(self::TABLE)->insert([
            'role' => 'client',
        ]);
        DB::table(self::TABLE)->insert([
            'role' => 'dealer',
        ]);
        DB::table(self::TABLE)->insert([
            'role' => 'distributor',
        ]);
    }
}
