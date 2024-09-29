<?php

namespace App\Repository\Product;

use App\Models\Product;
use Illuminate\Http\Request;

interface ProductRepositoryInterface
{
    public function display($name, $maincategoryid, $categoryid, $subcategoryid, $pagination, $user);
    public function admindisplay($pagination);
    public function displaybyid($id);
    public function create(Request $request);
    public function notactive($id);
    public function active($id);
}
