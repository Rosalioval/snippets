<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    public function brand()
    {
        return $this->hasOne('\App\ModelAdapters\BrandAdapter', 'id', 'brand_id');
    }

    public function cart()
    {
        return $this->belongsTo('\App\ModelAdapters\ProductsCartAdapter', 'id', 'product_id');
    }
}
