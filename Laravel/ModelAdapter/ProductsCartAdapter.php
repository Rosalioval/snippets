<?php

namespace App\ModelAdapters;

use App\ProductsCart;
use App\ModelAdapters\ProductAdapter as Product;

class ProductsCartAdapter extends ProductsCart
{
    public function setProductsAmount(String $cookie, Array $products)
    {
        foreach ($products as $id => $amount) {

            $this->where('session', $cookie)
                ->where('product_id', $id)
                ->update(['amount' => $amount]);
        }

        return $this;
    }

    public function getProductsInSession(String $session)
    {
        $productsInCart = $this->where('session', $session)->get()->pluck('product_id');
        $products = Product::whereIn('id', $productsInCart)->get();

        return $products;
    }
}
