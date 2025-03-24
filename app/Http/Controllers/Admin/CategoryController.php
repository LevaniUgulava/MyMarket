<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductSize;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Clothsize;
use App\Models\Color;
use App\Models\Maincategory;
use App\Models\Product;
use App\Models\Shoessize;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function displaymain()
    {
        $maincategory = Maincategory::select('id', 'name')->get();
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
    public function Maincategorybyid($id)
    {
        $maincategory = Maincategory::select('id', 'name')->findorfail($id);
        return response()->json([
            "id" => $maincategory->id,
            "name" => $maincategory->name
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
    public function categorybyid($id)
    {
        $category = Category::findorfail($id);
        return response()->json([
            "id" => $category->id,
            "name" => $category->name

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
    public function subcategorybyid($id)
    {
        $subcategory = Subcategory::findorfail($id);
        return response()->json([
            "id" => $subcategory->id,
            "name" => $subcategory->name

        ]);
    }
    public function displaycolor()
    {
        $colors = Color::whereHasMorph(
            "colorable",
            [Shoessize::class, Clothsize::class]
        )->with(
            [
                'colorable' => function ($query) {
                    $query->select("id", "product_id");
                }
            ]
        )->select("id", "color", "colorable_id", "colorable_type")
            ->get()
            ->map(function ($color) {
                $product_id = $color->colorable->product_id;
                $product = Product::select("maincategory_id", "category_id", "subcategory_id")
                    ->findOrFail($product_id);


                return  [
                    "id" => $color->id,
                    "color" => $color->color,
                    "maincategory_id" => $product->maincategory_id,
                    "category_id" => $product->category_id,
                    "subcategory_id" => $product->subcategory_id,

                ];
            });


        return response()->json($colors);
    }
    public function displaysize()
    {
        $clothsize = Clothsize::distinct()->orderByRaw("FIELD(size, 'xxs', 'xs', 's', 'm', 'l', 'xl', 'xxl')")
            ->pluck("size");
        $shoessize = Shoessize::distinct()
            ->orderBy('size')
            ->pluck('size');

        return response()->json(['clothsize' => $clothsize, "shoessize" => $shoessize]);
    }
}
