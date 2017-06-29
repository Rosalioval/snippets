<?php

use Illuminate\Database\Seeder;

class LuAddressCountriesSeeder extends Seeder
{
    const TABLE = 'lu_address_countries';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(self::TABLE)->insert([
            'country' => 'México',
        ]);
    }
}
