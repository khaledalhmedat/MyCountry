<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected $users;

    public function __construct(UserRepositoryInterface $users)
    {
        $this->users = $users;
    }

    public function login(Request $request)
{
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    // استدعاء الريبو
    $response = $this->users->login($data);

    // رجّع الريسبونس كما هو (يتضمن الـ OTP)
    return response()->json($response, $response['status'] ? 200 : 401);
}

public function verifyOtp(Request $request)
{
    $data = $request->validate([
        'email' => 'required|email',
        'otp'   => 'required|numeric'
    ]);

    $response = $this->users->verifyOtp($data);

    return response()->json($response, $response['status'] ? 200 : 400);
}


}
