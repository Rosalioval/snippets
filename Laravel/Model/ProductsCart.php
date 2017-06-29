<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductsCart extends Model
{
    protected $table = 'products_cart';

    public function product()
    {
        return $this->hasOne('\App\ModelAdapters\ProductAdapter', 'id', 'product_id');
    }

    public function getAmountAttribute($value)
    {
        return $value == 0 ? 1 : $value;
    }
}
