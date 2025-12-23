<?php
namespace App\Support;

class Cart
{
    private const KEY = 'pos_cart';

    public static function get(): array
    {
        $cart = session(self::KEY, null);

        if ($cart === null) {
            $legacy = session('cart', null);
            if ($legacy instanceof \Illuminate\Support\Collection) {
                $legacy = $legacy->toArray();
            }

            if (is_array($legacy)) {
                if (isset($legacy['lines']) && is_array($legacy['lines'])) {
                    $cart = $legacy;
                } elseif (isset($legacy[0]) && is_array($legacy[0]) && isset($legacy[0]['product_id'])) {
                    $cart = ['lines' => collect($legacy)->keyBy('product_id')->toArray()];
                } elseif (!isset($legacy['lines'])) {
                    $cart = ['lines' => $legacy]; 
                }
            }
        }

        if (!is_array($cart) || !isset($cart['lines']) || !is_array($cart['lines'])) {
            $cart = ['lines' => []];
        }

        return $cart;
    }

    public static function put(array $cart): void
    {
        if (!isset($cart['lines']) || !is_array($cart['lines'])) {
            $cart = ['lines' => []];
        }
        session([self::KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
        session()->forget('cart');
    }

    public static function totals(array $cart): array
    {
        $total = 0.0;
        foreach ($cart['lines'] as $line) {
            $total += ((float)$line['price']) * ((float)$line['qty']);
        }
        return ['total' => $total];
    }
}
