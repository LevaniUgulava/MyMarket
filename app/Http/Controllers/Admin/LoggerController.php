<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sitelog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoggerController extends Controller
{
    public function display(Request $request)
    {
        $action = $request->query("action");
        $user = $request->query('user');
        $logs = Sitelog::with('user')
            ->Action($action)
            ->User($user)
            ->get()->map(function ($log) {
                $product = Product::where('id', $log->model_id)->first();
                $user = User::where("email", $log->user->email)->first();
                $currentrole = implode(', ', $user->getRoleNames()->toArray());
                return [
                    "id" => $log->id,
                    "action" => $log->action,
                    "model" => $log->model,
                    "product" => $product ,
                    "role" => $log->role,
                    "current_role" => $currentrole,
                    "user_email" => $log->user->email,
                    "created_at" => $log->created_at->format('Y-m-d H:i'),
                    "updated_at" => $log->updated_at->format('Y-m-d H:i'),

                ];
            });


        return response()->json($logs);
    }
}
