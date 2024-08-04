<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'discount' => $this->discount,
            'discountprice' => $this->discountprice,
            'MainCategory' => $this->MainCategory->name,
            'Category' => $this->Category->name,
            'SubCategory' => $this->Subcategory->name,
            'Contacts' => $this->Contacts->pluck('number'),
            'image_urls' => $this->getMedia('default')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name); // Ensure full URL is returned
            }),
            'active' => $this->active
        ];
    }
}
