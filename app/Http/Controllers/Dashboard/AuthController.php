<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (!Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])) {
            return response([
                'message' => "Wrong credentials",
                "data" => errors()
            ], 401);
        }

        $user = UserAdmin::query()
            ->where('email', $request->email)
            ->first();

        $token = $user ->createToken('token')->plainTextToken;

        return response([
            "message" => "Request succeeded",
            "data" => [
                "token" => $token
            ]
        ],201);
    }
}
