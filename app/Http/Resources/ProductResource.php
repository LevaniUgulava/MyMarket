<?php

namespace App\Http\Resources;

use App\Helpers\Translator;
use App\Http\Controllers\DiscountController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Type\Decimal;

class ProductResource extends JsonResource
{
    protected $user;

    public function __construct($resource, $user = null)
    {
        parent::__construct($resource);
        $this->user = $user;
    }

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
            'size_type' => $this->size_type,
            'size' => $this->getSizeData(),
            'discount' => $this->discount,
            'discountstatus' => $this->discountstatus,
            'discountprice' => $this->discountprice,
            'MainCategory' => $this->MainCategory,
            'Category' => $this->Category,
            'SubCategory' => $this->Subcategory,
            'additionalinfo' => $this->additionalinfo,
            'image_urls' => $this->getMedia('default')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            }),
            'active' => $this->active,
            'isLiked' => $this->isLiked ?? false,
            'isRated' => $this->isRated ?? false,
            'Rate' => number_format((float)$this->rateproduct_avg_rate, 1),
            'MyRate' => (float)$this->MyRate

        ];
    }

    protected function getSizeData()
    {
        if ($this->clothsize->isNotEmpty()) {
            return $this->clothsize->map(function ($size) {
                return [
                    'size' => $size->size,
                    'details' => $size->colors->map(function ($color) {
                        return [
                            'color' => $color->color,
                            'quantity' => $color->quantity
                        ];
                    }),
                ];
            });
        }
        if ($this->shoesize->isNotEmpty()) {
            return $this->shoesize->map(function ($size) {
                return [
                    'size' => $size->size,
                    'details' => $size->colors->map(function ($color) {
                        return [
                            'color' => $color->color,
                            'quantity' => $color->quantity
                        ];
                    }),
                ];
            });
        }


        return null;
    }
}
