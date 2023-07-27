<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
//        $validator = Validator::make($request->all(), [ // 1-ci yolu budur.
//            'email' => 'required|unique:users,id',
//            'password' => 'required',
//            'name' => 'required',
//        ]);
//
//        if ($validator->fails()) {
//            return response([
//                'message' => "Error.",
//                "data" => $validator->errors()
//            ]);
//        }
//        $checkUser = User::query() // 2-ci yolu budur.
//            ->where("email", $request->email)
//            ->exists();
//        if ($checkUser) {
//            return "This email exists";
//        }

        User::query()
            ->create([
                'email' => $request->email,
                'password' =>$request->password,
                'name' =>$request->name
            ]);

        return response([
            'message' => "Successfully created.",
            "data" => null
        ], 200);

    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response([
                'message' => "Wrong credentials",
                "data" => errors()
            ], 401);
        }

        $user = User::query()
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
