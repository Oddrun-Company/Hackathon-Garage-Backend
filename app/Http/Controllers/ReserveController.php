<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReserveController extends Controller
{
    public function list(Request $request)
    {
        // $user = $request->user()->only(['name', 'debt']);
        $user = User::where('id', '=', 1)
            ->first()
            ->only(['name', 'debt']);

        return [
            'active_tab' => 'current',
            'user' => $user,
            'current' => [
            ],
            'next' => [
            ]
        ];
    }
}
