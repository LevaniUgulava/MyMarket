<?php

namespace App\Repository\Product;

use Illuminate\Http\Request;

interface ProductRepositoryInterface
{
    public function display();
    public function create(Request $request);
}
