<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\ForgetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{


    public function Updateprofile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
        ]);
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        return response()->json([
            'message' => 'User Profile Updated',
        ]);
    }
    public function Updatepassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 404);
        }
        $request->validate([
            'oldpassword' => 'required|string|min:6',
            'newpassword' => 'required|string|min:6',
        ]);
        if (Hash::check($request->oldpassword, $user->password)) {
            $update = $user->update([
                'password' => $request->newpassword
            ]);
            if ($update) {
                return response()->json('Password Updated succesfully');
            }
        } else {
            return response()->json('the current password is invalid', 404);
        };
    }


    public function getprofile()
    {
        $user = Auth::user();

        return response()->json([
            'user' => $user
        ]);
    }
    public function likeproduct()
    {
        $user = Auth::user();
        $products = $user->manyproducts()->withAvg('rateproduct', 'rate')->get();

        $likedProductIds = $user ? $user->manyproducts()->pluck('product_id')->toArray() : [];
        $products->transform(function ($product) use ($likedProductIds, $user) {
            $product->image_urls = $product->getMedia('default')->map(fn($media) => $media->getUrl());
            $product->isLiked = in_array($product->id, $likedProductIds);

            if ($user) {
                $isEligible =  $product->eligibleStatuses()->wherePivot('userstatus_id', $user->userstatus->id)->withPivot('discount')->first();
                if ($isEligible && $user->userstatus->isActive()) {
                    $product->discountstatus = [
                        'userstatus' => $user->userstatus,
                        'discount' => $isEligible->pivot->discount
                    ];
                    $product->discountstatusprice = $user->getPriceByStatus($product, $product->price, $product->price);
                } else {
                    $product->discountstatus = null;
                    $product->discountstatusprice = $product->discountprice;
                }
            } else {
                $product->discountstatus = null;
                $product->discountstatusprice = $product->discountprice;
            }

            return $product;
        });
        return ProductResource::collection($products);
    }

    public function resendverification()
    {
        $user = Auth::user();
        $user->notify(new CustomVerifyEmail());
        return response()->json([
            "verification" => [
                'ka' => 'ვერიფიკაციის მეილი გაგზავნილია',
                'en' => "Verification Mail is Sent"
            ]
        ]);
    }

    public function ResetPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email|max:255"
        ]);
        $user = User::where("email", $request->email)->first();

        if ($user) {
            $token = Str::random(30);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $token,
                    'created_at' => now()
                ]
            );
            $user->notify(new ForgetPassword($token));
            return response()->json([
                "message" => [
                    "success" => true,
                    'messagekey' => "MailSented"
                ]
            ]);
        }
        return response()->json([
            "message" => [
                "success" => false,
                'messagekey' => "UsernotExist"
            ]
        ]);
    }

    public function ForgotPassword(Request $request, $id)
    {
        $request->validate([
            "password" => "required|string|max:255"
        ]);
        $token = $request->query('token');

        $user = User::where("id", $id)->first();
        if (!$user) {
            return response()->json([
                "success" => false,
                'messagekey' => "UsernotExist"
            ], 404);
        }

        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        if (!$record) {
            return response()->json([
                "success" => false,
                'messagekey' => "Invalidtoken"
            ], 400);
        }
        if ($token != $record->token) {
            return response()->json([
                "success" => false,
                'messagekey' => "Invalidtoken"
            ], 400);
        }
        $expiryTime = now()->subMinutes(15);

        if ($record->created_at < $expiryTime) {
            return response()->json([
                "success" => false,
                'messagekey' => "Expiredtoken"
            ], 400);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return response()->json([
            "success" => true,
            'messagekey' => "PasswordChangeSuccess"
        ]);
    }
}
