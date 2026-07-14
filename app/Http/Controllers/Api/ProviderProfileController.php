<?php

namespace App\Http\Controllers\Api;

use App\Enums\ActivityStatus;
use App\Enums\BadgeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreProviderProfileRequest;
use App\Http\Resources\ProviderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Provider;
use App\Support\ActorProfile;
use App\Support\SecurityAccountProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderProfileController extends Controller
{
    public function __construct(private SecurityAccountProvisioner $securityAccounts) {}

    public function store(StoreProviderProfileRequest $request): JsonResponse
    {
        $actor = new ActorProfile($request->user());

        if ($actor->providerOrNull() !== null) {
            return ApiResponse::error('Provider profile already exists.', JsonResponse::HTTP_CONFLICT);
        }

        $this->securityAccounts->ensureForUser($request->user());

        $provider = Provider::query()->create([
            'security_account_id' => $request->user()->id,
            'current_badge' => BadgeType::Grey,
            'badge_modified_at' => now(),
            'badge_expires_at' => now()->addYear(),
            'activity_status' => ActivityStatus::Available,
        ]);

        $provider->serviceCategories()->attach($request->input('service_category_ids'));

        return ApiResponse::createdResource(
            new ProviderResource($provider->load('serviceCategories')),
        );
    }

    public function show(Request $request): JsonResponse
    {
        return ApiResponse::resource(
            new ProviderResource(
                (new ActorProfile($request->user()))->provider()->load('serviceCategories'),
            ),
        );
    }

    /**
     * List all available providers (activity_status = 'available')
     * with their basic info and service categories.
     */
    public function available(): JsonResponse
    {
        $providers = Provider::query()
            ->where('activity_status', ActivityStatus::Available->value)
            ->with(['serviceCategories', 'securityAccount.user'])
            ->latest()
            ->get();

        return ApiResponse::resource(
            ProviderResource::collection($providers),
        );
    }
}
