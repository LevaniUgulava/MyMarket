<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Maincategory;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function displaymain()
    {
        $maincategory = Maincategory::all();
        return $maincategory;
    }

    public function Maincategory(Request $request)
    {
        Maincategory::create([
            'name' => $request->name,
        ]);
        return response()->json([
            'message' => 'MainCategory Added',
        ]);
    }
    public function Maincategorydelete($id)
    {
        $maincategory = Maincategory::where('id', $id)->first();
        $maincategory->delete();
        return response()->json([
            'message' => 'MainCategory Deleted',

        ]);
    }

    public function displaycategory()
    {
        $Category = Category::with('Maincategory')->get();
        return $Category;
    }

    public function category(Request $request)
    {
        Category::create([
            'name' => $request->name,
            'maincategory_id' => $request->maincategory_id
        ]);
        return response()->json([
            'message' => 'Category Added',
        ]);
    }
    public function categorydelete($id)
    {
        $category = Category::where('id', $id)->first();
        $category->delete();
        return response()->json([
            'message' => 'Category deleted',

        ]);
    }

    public function displaysub()
    {
        $Subcategory = Subcategory::with('Category', 'Maincategory')->get();
        return $Subcategory;
    }

    public function Subcategory(Request $request)
    {
        Subcategory::create([
            'name' => $request->name,
            'maincategory_id' => $request->maincategory_id,
            'category_id' => $request->category_id

        ]);
        return response()->json([
            'message' => 'SubCategory Added',
        ]);
    }
    public function Subcategorydelete($id)
    {
        $subcategory = Subcategory::where('id', $id)->first();
        $subcategory->delete();
        return response()->json([
            'message' => 'SubCategory deleted',

        ]);
    }
}
