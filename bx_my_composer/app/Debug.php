<?php

namespace App;

use Kint\Kint;

class Debug
{
    static $kint_instance = null;

    public static function D(...$args)
    {
        if (!class_exists('Kint\Kint')) {
            echo 'no kint!'; return;
        }

        Kint::dump($args);
    }
}
