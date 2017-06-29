<?php

use Illuminate\Database\Seeder;

class LuOrderStatusRolesSeeder extends Seeder
{
    const TABLE = 'lu_order_status';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(self::TABLE)->insert([
            'status' => 'pending',
        ]);
        DB::table(self::TABLE)->insert([
            'status' => 'paid',
        ]);
        DB::table(self::TABLE)->insert([
            'status' => 'in_transit',
        ]);
        DB::table(self::TABLE)->insert([
            'status' => 'delivered',
        ]);
        DB::table(self::TABLE)->insert([
            'status' => 'rejected',
        ]);
        DB::table(self::TABLE)->insert([
            'status' => 'returned',
        ]);
    }
}
