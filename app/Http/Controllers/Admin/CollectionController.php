<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;

class CollectionController extends Controller
{

    public function getcollection()
    {
        $Collections = Collection::all();
        $Collections->each(function ($collection) {
            $collection->media_urls = $collection->getMedia('collection')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });
        });
        return response()->json($Collections);
    }
    public function create(Request $request)
    {
        $validatedata = $request->validate([
            'title' => 'required|string',
            'headerColor' => 'nullable|string|size:7',
            'description' => 'nullable|string',
            'discount' => 'nullable|integer|min:0|max:100',
        ]);

        $Collection = Collection::create($validatedata);

        $Collection->addMultipleMediaFromRequest(['imageurl'])->each(function ($fileAdder) {
            $fileAdder->toMediaCollection('collection');
        });
        return response()->json([
            'message' => "create succefully"
        ]);
    }

    public function deletecollection(Collection $collection)
    {
        $collection->clearMediaCollection('collection');
        $collection->delete();
        return response()->json(['message' => 'Collection deleted successfully']);
    }
    public function singlecollection(Collection $collection)
    {
        $collection->load('products');

        return response()->json([
            'collection' => [
                'id' => $collection->id,
                'title' => $collection->title,
                'headerColor' => $collection->headerColor,
                'description' => $collection->description,
                'discount' => $collection->discount,
                'created_at' => $collection->created_at,
                'updated_at' => $collection->updated_at,
                'products' => ProductResource::collection($collection->products),
            ]
        ]);
    }




    public function addtocollection(Collection $collection, Product $product)
    {
        $collection->products()->attach($product->id);
        return response()->json(['message' => 'Product added to collection successfully']);
    }
}
