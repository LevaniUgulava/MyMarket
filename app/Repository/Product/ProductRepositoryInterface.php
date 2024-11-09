<?php

namespace App\Repository\Product;

use App\Models\Product;
use Illuminate\Http\Request;

interface ProductRepositoryInterface
{
    public function display($name, $maincategoryid, $categoryid, $subcategoryid, $pagination, $user, $section, $lang);
    public function admindisplay($name, $maincategoryid, $categoryid, $subcategoryid, $pagination);
    public function displaybyid($id, $user);
    public function create(Request $request);
    public function notactive($id);
    public function active($id);
}
