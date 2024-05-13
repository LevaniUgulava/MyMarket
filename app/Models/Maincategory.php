<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maincategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function Subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }
    public function Categories()
    {
        return $this->hasMany(Category::class);
    }

    public function Products()
    {
        return $this->hasMany(Product::class);
    }
}
