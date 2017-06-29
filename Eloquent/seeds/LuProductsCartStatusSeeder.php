<?php

use Illuminate\Database\Seeder;

class LuProductsCartStatusSeeder extends Seeder
{
    const TABLE = 'lu_products_cart_status';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(self::TABLE)->insert([
            'status' => 'in_session',
        ]);
        DB::table(self::TABLE)->insert([
            'status' => 'in_order',
        ]);
    }
}
