<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Userstatus;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\RegisterNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        $user->notify(new CustomVerifyEmail());

        $user->assignRole('default');
        $roles = $user->getRoleNames();

        return response()->json([
            'message' => 'Registered',
            'id' => $user->id,
            'roles' => $roles

        ]);
    }
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }
        $status = $user->userstatus->name;

        $token = $user->createToken('api', [], now()->addDays(3))->plainTextToken;
        $roles = $user->getRoleNames();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'token' => $token,
            'roles' => $roles,
            'status' => $status,
            'total_spent' => $user->total_spent
        ]);
    }

    public function adminlogin(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }
        if ($user->hasRole('default')) {
            return response()->json([
                'message' => 'havenot got access',
            ], 403);
        }
        $token = $user->createToken('api')->plainTextToken;
        $roles = $user->getRoleNames();
        return response()->json([
            'name' => $user->name,
            'token' => $token,
            'roles' => $roles
        ]);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function verify($id)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email verified successfully!'], 200);
    }

    public function getuserstatus()
    {
        $user = Auth::user();
        $userstatus = $user->userstatus;
        $statuses = Userstatus::orderby('toachieve')->get();

        $result = [];
        for ($i = 0; $i < count($statuses); $i++) {
            if (
                $user->total_spent >= $statuses[$i]->toachieve &&
                isset($statuses[$i + 1]) &&
                $user->total_spent <= $statuses[$i + 1]->toachieve
            ) {
                $result = [
                    'current' => $statuses[$i]->name,
                    'next' => $statuses[$i + 1]->name,
                ];
                break;
            }
        }

        return response()->json([
            'status' => $userstatus,
            'user' => $user->total_spent,
            'result' => $result

        ]);
    }
}
