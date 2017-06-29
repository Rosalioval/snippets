<?php

namespace App\ModelAdapters;

use App\LuAddressCountries;

class LuAddressCountriesAdapter extends LuAddressCountries
{
    public static function getOptions(): array
    {
        $self = new static;

        $options = [];


        foreach ($self->all() as $country) {
            $options[] = [
                'key' => $country->id,
                'value' => $country->country
            ];
        }

        return $options;
    }
}
