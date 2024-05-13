<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'maincategory_id'];

    public function Subcategory()
    {
        return $this->hasMany(Subcategory::class);
    }
    public function Products()
    {
        return $this->hasMany(Product::class);
    }

    public function Maincategory()
    {
        return $this->belongsTo(Maincategory::class);
    }
}
