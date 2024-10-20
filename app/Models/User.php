<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Events\RegisterNotification as EventsRegisterNotification;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\RegisterNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'confirmation_token',
        'email_verified_at'
    ];

    protected $dispatchesEvents = [
        'created' => EventsRegisterNotification::class
    ];




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];



    public function manyproducts()
    {
        return $this->belongsToMany(Product::class, 'user_product');
    }


    public function commentproduct()
    {
        return $this->belongsToMany(Product::class, 'product_comment')->withPivot('comment')->withTimestamps();
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function cartItems()
    {
        return $this->hasMany(Cart::class)
            ->join('cart_item', 'cart.id', '=', 'cart_item.cart_id')
            ->join('products', 'products.id', '=', 'cart_item.product_id')
            ->select('products.*', 'cart_item.quantity', 'cart_item.retail_price', 'cart_item.size', 'cart_item.total_price');
    }


    public function Sitelogs()
    {
        return $this->hasMany(Sitelog::class)->wherehas('user', function ($query) {
            $query->wherehas('roles', function ($q) {
                $q->where('name', '!=', 'default');
            });
        });
    }

    public function rateuser()
    {
        return $this->hasMany(RateProduct::class, 'user_id');
    }
}
