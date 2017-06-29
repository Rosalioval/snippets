<?php

namespace App\ModelAdapters;

use App\Order;
use App\ModelAdapters\ProductsCartAdapter as ProductsCart;
use App\ModelAdapters\ProductAdapter as Product;

class OrderAdapter extends Order
{
    public function calcTotals()
    {
        $cart = $this->getCart();

        $this->subtotal = 0.0;
        $this->discount = 0.0;
        $this->total = 0.0;

        foreach ($cart as $item) {
            $subtotal = ($item->product->price * $item->amount) / (1 + $item->product->discount);
            $this->subtotal += round($subtotal, 2);
            $total = $this->subtotal / (1 + 0.0);
            $this->total += round($total, 2);
        }

        return $this;
    }

    public function getCart()
    {
        $session = $this->products_cart_session;

        $cart = ProductsCart::where('session', $session)->get();

        return $cart;
    }

    public function getTax()
    {
        if ($this->tax) {
            $tax = $this->subtotal * (1 + $this->tax->tax);
            return round($tax, 2);
        }

        return 0.0;
    }

    public function updateProductsStock()
    {
        $session = $this->products_cart_session;

        $orderProducts = ProductsCart::where('session', $session)->get();

        foreach ($orderProducts as $item) {
            $product = Product::find($item->product_id);
            $product->stock -= $item->amount;
            $product->save();
        }

        return $this;
    }
}
