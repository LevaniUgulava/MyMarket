<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clothsize extends Model
{
    use HasFactory;
    protected $fillable = ['size', 'product_id'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($clothsize) {
            $clothsize->quantities()->delete();
        });
    }

    public function products()
    {
        return $this->belongsTo(Product::class);
    }


    public function quantities()
    {
        return $this->morphMany(Quantity::class, 'quantifiable');
    }
}
