<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Support\PhoneNumber;
use App\Support\SecurityAccountProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private SecurityAccountProvisioner $securityAccounts) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $phone = PhoneNumber::normalize($request->string('phone')->toString());

        $user = DB::transaction(function () use ($request, $phone): User {
            $user = User::query()->create([
                'name' => $request->string('name')->toString(),
                'phone' => $phone,
                'password' => $request->string('password')->toString(),
            ]);

            $this->securityAccounts->ensureForUser(
                $user,
                $request->string('device_fingerprint')->toString(),
                $request->string('national_id_hash')->toString(),
            );

            return $user;
        });

        $token = $user->createToken($request->string('device_name')->toString());

        return ApiResponse::created(
            new UserResource($user),
            ['token' => $token->plainTextToken],
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $phone = PhoneNumber::normalize($request->string('phone')->toString());

        if (! Auth::attempt([
            'phone' => $phone,
            'password' => $request->string('password')->toString(),
        ])) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        $token = $user->createToken($request->string('device_name')->toString());

        return ApiResponse::success(
            new UserResource($user),
            meta: ['token' => $token->plainTextToken],
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return ApiResponse::message('Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::resource(new UserResource($request->user()));
    }
}
