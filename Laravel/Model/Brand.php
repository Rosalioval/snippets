<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Utils\StringTool;

class Brand extends Model
{
    protected $table = 'product_brands';

    /*
     * Mutators
     *
     * */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = StringTool::normalizeNames($value);
    }
}
