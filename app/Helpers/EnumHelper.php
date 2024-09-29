<?php

namespace App\Helpers;

use App\Enums\ProductSize;

class EnumHelper
{
    public static function GetSizesAsArray()
    {
        $sizes = ProductSize::cases();
        return $sizes;
    }
}
