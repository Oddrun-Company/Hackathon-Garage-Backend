<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class AuthController extends Controller
{
    private const CODE_CACHE_PREFIX = 'auth:request:code:';

    public function request(Request $request, SmsService $sms)
    {
        $phone = $request->get('phone');

        $code = random_int(1000, 9999);
        Cache::set(self::CODE_CACHE_PREFIX . $phone, $code);

        try {
            $sms->otp($phone, $code);
        } catch (RuntimeException $exception) {
            return response()->base(false, message: 'Boom!');
        }

        return response()->base();
    }

    public function verify(Request $request)
    {
        $code = $request->get('code');
        $phone = $request->get('phone');
        $otp = Cache::get(self::CODE_CACHE_PREFIX . $phone);
        Cache::forget(self::CODE_CACHE_PREFIX . $phone);
        if (!is_null($code) && $otp != $code) {
            return response()->base(false, 'invalid code');
        }
        // retrieve use and generate token
        $user = User::query()->where('phone_number', $phone)->first();
        return response()->base(true, [
            'user' => new UserResource($user),
            'token' => $user->createToken(env('APP_KEY'))->plainTextToken
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return response()->base();
    }
}
