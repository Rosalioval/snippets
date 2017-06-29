<?php

use Illuminate\Database\Seeder;

class LuProductStatus extends Seeder
{
    const TABLE = 'lu_product_status';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(self::TABLE)->insert([
            'status' => 'active',
        ]);
        DB::table(self::TABLE)->insert([
            'status' => 'private',
        ]);
        DB::table(self::TABLE)->insert([
            'status' => 'inactive',
        ]);
    }
}
