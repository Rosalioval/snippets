<?php

use Illuminate\Database\Seeder;

class LuAddressStatesSeeder extends Seeder
{
    const TABLE = 'lu_address_states';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(self::TABLE)->insert([
            'country_id' => 1,
            'state' => 'Ciudad de México',
        ]);
    }
}
