<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $guarded = [];

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


    public function Contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_product');
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
}
