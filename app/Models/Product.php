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

    public function commentusers()
    {
        return $this->belongsToMany(User::class, 'product_comment')->withPivot('comment')->withTimestamps();
    }
    public function orderuser()
    {
        return $this->belongsToMany(User::class, 'cart')->withPivot('quantity', 'total_price');
    }
}
