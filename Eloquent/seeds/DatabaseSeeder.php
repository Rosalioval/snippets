<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LuOrderStatusRolesSeeder::class);
        $this->call(LuUserRolesSeeder::class);
        $this->call(LuProductsCartStatusSeeder::class);
        $this->call(LuProductTaxSchemaSeeder::class);
        $this->call(LuProductStatus::class);
        $this->call(LuAddressCountriesSeeder::class);
        $this->call(LuAddressStatesSeeder::class);
    }
}
