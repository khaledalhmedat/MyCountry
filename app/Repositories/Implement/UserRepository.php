<?php

namespace App\Repositories\Implement;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use App\Models\OtpCode;

class UserRepository implements UserRepositoryInterface
{
   

public function register(array $data)
{
    // Create user
    $user = User::create([
        'full_name'    => $data['full_name'],
        'email'        => $data['email'],
        'phone_number' => $data['phone_number'],
        'password'     => Hash::make($data['password']),
        'address'      => $data['address'],
    ]);

    // Generate OTP
    $otpCode = rand(100000, 999999);

    OtpCode::create([
        'user_id'    => $user->id,
        'code'       => $otpCode,
        'expires_at' => Carbon::now()->addMinutes(10),
    ]);

    // == Send OTP via Email ==
    Mail::to($user->email)->send(new SendOtpMail($otpCode));

    // Generate token (Sanctum)
    $token = $user->createToken('mobile_token')->plainTextToken;

    return [
        'status' => true,
        'message' => "Account created. OTP sent to your email.",
        'user'  => $user,
        'token' => $token,
        'otp'   => $otpCode, // لأغراض التست فقط
    ];
}



 

public function login(array $data)
{
    // البحث عن المستخدم
    $user = User::where('email', $data['email'])->first();

    if (! $user) {
        return [
            'status' => false,
            'message' => 'User not found.'
        ];
    }

    // تحقق كلمة المرور
    if (! Hash::check($data['password'], $user->password)) {
        return [
            'status' => false,
            'message' => 'Invalid credentials.'
        ];
    }

    // إنشاء OTP جديد
    $otpCode = rand(100000, 999999);

    OtpCode::updateOrCreate(
        ['user_id' => $user->id],
        [
            'code'       => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10)
        ]
    );

    // إرسال الإيميل
    Mail::to($user->email)->send(new SendOtpMail($otpCode));

    // إنشاء توكن
    $token = $user->createToken('mobile_token')->plainTextToken;

    return [
        'status' => true,
        'message' => 'Logged in successfully. OTP sent to your email.',
        'user' => $user,
        'token' => $token,
        'otp' => $otpCode  // احذفها بالمستقبل بعد الاختبار
    ];
}


public function verifyOtp(array $data)
{
    // 1. البحث عن المستخدم
    $user = User::where('email', $data['email'])->first();

    if (! $user) {
        return [
            'status' => false,
            'message' => 'User not found.'
        ];
    }

    // 2. البحث عن آخر رمز OTP
    $otp = OtpCode::where('user_id', $user->id)
                  ->where('code', $data['otp'])
                  ->orderBy('created_at', 'desc')
                  ->first();

    if (! $otp) {
        return [
            'status' => false,
            'message' => 'Invalid OTP code.'
        ];
    }

    // 3. التحقق من انتهاء الصلاحية
    if (Carbon::now()->greaterThan($otp->expires_at)) {
        return [
            'status' => false,
            'message' => 'OTP has expired.'
        ];
    }

    // 4. تفعيل المستخدم (إذا ما كان مفعّل)
    if (! $user->email_verified_at) {
        $user->email_verified_at = Carbon::now();
        $user->save();
    }

    // 5. حذف كود OTP بعد الاستخدام (اختياري لكنه الأفضل)
    $otp->delete();

    // 6. إنشاء توكن جديد (إذا بدك)
    $token = $user->createToken('mobile_token')->plainTextToken;

    return [
        'status' => true,
        'message' => 'OTP verified successfully.',
        'user' => $user,
        'token' => $token
    ];
}




}
