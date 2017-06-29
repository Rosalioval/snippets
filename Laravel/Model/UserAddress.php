<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'user_address';

    public function country()
    {
        return $this->hasOne('\App\ModelAdapters\LuAddressCountryAdapter', 'id', 'country_id');
    }
}
