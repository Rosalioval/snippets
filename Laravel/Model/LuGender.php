<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LuGender extends Model
{
    const MALE = 1;
    const FEMALE = 2;

    const OPTIONS = [
        [
            'key' => self::MALE,
            'value' => 'Masculino',
        ],
        [
            'key' => self::FEMALE,
            'value' => 'Femenino',
        ]
    ];
}
