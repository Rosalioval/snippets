<?php

use Illuminate\Database\Seeder;

class LuProductTaxSchemaSeeder extends Seeder
{
    const TABLE = 'lu_product_tax_schema';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(self::TABLE)->insert([
            'tax' => 0.16,
        ]);
    }
}
