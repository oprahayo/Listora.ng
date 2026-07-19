<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\Contracts\OtpDispatcher;
use App\Domain\Auth\PhoneNormalizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\OtpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    public function store(OtpRequest $request, OtpDispatcher $dispatcher): JsonResponse
    {
        $validated = $request->validated();
        $identifier = filter_var($validated['identifier'], FILTER_VALIDATE_EMAIL)
            ? Str::lower($validated['identifier'])
            : PhoneNormalizer::normalize($validated['identifier']);

        $dispatcher->request($identifier, $validated['role']);

        return response()->json([
            'message' => 'OTP sign-in is currently unavailable.',
        ]);
    }
}
