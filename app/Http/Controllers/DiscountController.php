<?php

namespace App\Http\Controllers;

use App\Models\Userstatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscountController extends Controller
{
    public function updateStatus($user)
    {
        $status = Userstatus::where('toachieve', '<=', $user->total_spent)
            ->orderBy('toachieve', 'desc')
            ->first();

        if ($status && $status->id !== $user->userstatus_id) {
            $user->userstatus_id = $status->id;
            $user->save();
        }
    }
}
