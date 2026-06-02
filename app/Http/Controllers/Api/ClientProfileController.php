<?php

namespace App\Http\Controllers\Api;

use App\Enums\ClientType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientProfileRequest;
use App\Http\Resources\ClientResource;
use App\Http\Responses\ApiResponse;
use App\Models\Client;
use App\Support\ActorProfile;
use App\Support\SecurityAccountProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientProfileController extends Controller
{
    public function __construct(private SecurityAccountProvisioner $securityAccounts) {}

    public function store(StoreClientProfileRequest $request): JsonResponse
    {
        $actor = new ActorProfile($request->user());

        if ($actor->clientOrNull() !== null) {
            return ApiResponse::error('Client profile already exists.', JsonResponse::HTTP_CONFLICT);
        }

        $this->securityAccounts->ensureForUser($request->user());

        $client = Client::query()->create([
            'security_account_id' => $request->user()->id,
            'client_type' => ClientType::from($request->string('client_type')->toString()),
        ]);

        return ApiResponse::createdResource(new ClientResource($client));
    }

    public function show(Request $request): JsonResponse
    {
        return ApiResponse::resource(
            new ClientResource((new ActorProfile($request->user()))->client()),
        );
    }
}
