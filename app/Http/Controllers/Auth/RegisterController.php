<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    protected $users;

    public function __construct(UserRepositoryInterface $users)
    {
        $this->users = $users;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone_number' => 'required|numeric|unique:users,phone_number',
            'password'     => 'required|min:6',
            'address'      => 'required|string',
        ]);

        $result = $this->users->register($validated);

        return response()->json([
            'status' => true,
            'message' => 'Account created successfully.',
            'user' => $result['user'],
            'token' => $result['token'],
            'otp'   => $result['otp'], 
        ], 201);
    }
}
