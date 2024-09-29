<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
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
        $user->assignRole('default');
        $user->notify(new RegisterNotification());
        $roles = $user->getRoleNames();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Registered',
            'id' => $user->id,
            'token' => $token,
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


        $token = $user->createToken('api', [], now()->addDays(3))->plainTextToken;
        $roles = $user->getRoleNames();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'token' => $token,
            'roles' => $roles
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
}
