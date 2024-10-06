<?php

namespace App\Models;

use App\Enums\ProductSize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $guarded = [];
    protected $casts = [
        'size' => 'array',
    ];




    public function scopeSearchname($query, $name)
    {
        if ($name) {
            return $query->where('name', 'LIKE', '%' . $name . '%');
        }

        return $query;
    }

    public function scopeSearchmain($query, $maincategoryid)
    {
        if ($maincategoryid) {
            return $query->where('maincategory_id', $maincategoryid);
        }
    }
    public function scopeSearchcategory($query, $categoryid)
    {
        if ($categoryid) {
            return $query->where('category_id', $categoryid);
        }
    }
    public function scopeSearchsubcategory($query, $subcategoryid)
    {
        if ($subcategoryid) {
            return $query->where('category_id', $subcategoryid);
        }
    }


    public function Category()
    {
        return $this->belongsTo(Category::class);
    }
    public function Subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
    public function Maincategory()
    {
        return $this->belongsTo(Maincategory::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function users()
    {
        return $this->belongsToMany(User::class, 'user_product');
    }

    public function commentusers()
    {
        return $this->belongsToMany(User::class, 'product_comment')->withPivot('comment')->withTimestamps();
    }


    public function shoesize()
    {
        return $this->hasMany(Shoessize::class);
    }

    public function clothsize()
    {
        return $this->hasMany(Clothsize::class);
    }


    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_item')
            ->join()
            ->withPivot('quantity', 'retail_price')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_item')
            ->withPivot('quantity', 'size', 'retail_price', 'total_price')
            ->withTimestamps();
    }
}
