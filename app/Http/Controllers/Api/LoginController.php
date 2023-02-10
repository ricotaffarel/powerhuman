<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * index
     *
     * @param  mixed $request
     * @return void
     */
    public function index(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'success'   => false,
                'message' => ['These credentials do not match our records.']
            ], 404);
        }

        $token = $user->createToken('ApiToken')->plainTextToken;

        $response = [
            'success'   => true,
            'user'      => $user,
            'token'     => $token
        ];

        return response([
            'success' => $user,
            'token' => $token,
            'message' => ['These credentials do not match our records.']
        ], 404);
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'min:8',
            'name' => 'required'
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name
        ]);

        return response()->json([
            'success'    => $user
        ], 200);
    }

    /**
     * logout
     *
     * @return void
     */
    public function logout()
    {
        auth()->logout();
        return response()->json([
            'success'    => true
        ], 200);
    }
}
