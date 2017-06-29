<?php

namespace App\ModelAdapters;

use App\UserAddress;

class UserAddressAdapter extends UserAddress
{
    public function getAddressString(): string
    {
        return "{$this->street}, {$this->interior}. {$this->neighborhood}, {$this->city}. {$this->country->country} {$this->zip_code}";
    }
}
