<?php

namespace App\Http\Controllers\Api;

use App\Enums\LifecycleStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Http\Requests\Mission\AssignProviderRequest;
use App\Http\Requests\Mission\CheckInRequest;
use App\Http\Requests\Mission\CompleteMissionRequest;
use App\Http\Requests\Mission\LockEscrowRequest;
use App\Http\Requests\Mission\PairMissionRequest;
use App\Http\Requests\Mission\StoreMissionRequest;
use App\Http\Resources\EscrowLedgerResource;
use App\Http\Resources\MissionApplicationResource;
use App\Http\Resources\MissionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Mission;
use App\Models\Provider;
use App\Services\EscrowService;
use App\Services\MissionWorkflowService;
use App\Support\ActorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class MissionController extends Controller
{
    public function __construct(
        private MissionWorkflowService $workflow,
        private EscrowService $escrow,
    ) {}

    /**
     * List only published (unassigned) missions — the public marketplace view.
     * For role-specific mission lists, use dedicated endpoints.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Mission::query()
            ->with(['serviceCategory', 'client', 'provider', 'escrowLedger'])
            ->where('lifecycle_status', LifecycleStatus::Published->value);

        if ($request->filled('service_category_id')) {
            $query->where('service_category_id', $request->string('service_category_id')->toString());
        }

        return MissionResource::collection(
            $query->latest()->paginate(20)
        );
    }

    public function store(StoreMissionRequest $request): JsonResponse
    {
        $client = (new ActorProfile($request->user()))->client();
        $validated = $request->validated();

        $mission = $this->workflow->createForClient($client, [
            'service_category_id' => $validated['service_category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'intervention_address' => $validated['intervention_address'],
            'estimated_price' => $validated['estimated_price'],
            'urgency_level' => $validated['urgency_level'] ?? 'normal',
            'execution_mode' => $validated['execution_mode'],
            'scheduled_at' => $validated['scheduled_at'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return ApiResponse::createdResource(new MissionResource($mission));
    }

    public function show(Request $request, Mission $mission): JsonResponse
    {
        // Optional authentication: resolve user from Bearer token if present
        // without requiring it — published missions are public, assigned ones
        // need auth (enforced by MissionPolicy::view())
        if ($request->bearerToken() && $request->user() === null) {
            $user = Auth::guard('sanctum')->user();
            if ($user) {
                Auth::setUser($user);
            }
        }

        $this->authorize('view', $mission);

        return ApiResponse::resource(
            new MissionResource(
                $mission->load(['serviceCategory', 'client', 'provider', 'escrowLedger', 'fieldVerification', 'applications']),
            ),
        );
    }

    public function lockEscrow(LockEscrowRequest $request, Mission $mission): JsonResponse
    {
        $client = Client::query()
            ->where('security_account_id', $request->user()->id)
            ->whereKey($mission->client_id)
            ->firstOrFail();

        $ledger = $this->escrow->lockForMission(
            $mission,
            $client,
            $request->has('payment_reference') ? $request->string('payment_reference')->toString() : null,
        );

        return ApiResponse::createdResource(new EscrowLedgerResource($ledger));
    }

    public function applications(Request $request, Mission $mission): AnonymousResourceCollection
    {
        $this->authorize('viewApplications', $mission);

        return MissionApplicationResource::collection(
            $mission->applications()
                ->with(['provider.securityAccount.user', 'provider.serviceCategories'])
                ->latest()
                ->get(),
        );
    }

    public function apply(Request $request, Mission $mission): JsonResponse
    {
        $application = $this->workflow->apply(
            $mission,
            (new ActorProfile($request->user()))->provider(),
        );

        return ApiResponse::createdResource(new MissionApplicationResource($application));
    }

    public function assign(AssignProviderRequest $request, Mission $mission): JsonResponse
    {
        $this->ensureClientOwnsMission($request, $mission);

        $provider = Provider::query()->findOrFail($request->string('provider_id')->toString());

        $mission = $this->workflow->assign($mission, $provider);

        return ApiResponse::resource(new MissionResource($mission));
    }

    public function checkIn(CheckInRequest $request, Mission $mission): JsonResponse
    {
        $mission = $this->workflow->checkIn(
            $mission,
            (new ActorProfile($request->user()))->provider(),
            $request->float('latitude'),
            $request->float('longitude'),
            $request->string('selfie_proof_url')->toString(),
        );

        return ApiResponse::resource(new MissionResource($mission));
    }

    public function pair(PairMissionRequest $request, Mission $mission): JsonResponse
    {
        $mission = $this->workflow->pair(
            $mission,
            (new ActorProfile($request->user()))->provider(),
            $request->string('pairing_code')->toString(),
        );

        return ApiResponse::resource(new MissionResource($mission));
    }

    public function complete(CompleteMissionRequest $request, Mission $mission): JsonResponse
    {
        $mission = $this->workflow->complete(
            $mission,
            (new ActorProfile($request->user()))->provider(),
            $request->input('after_photo_urls'),
        );

        return ApiResponse::resource(new MissionResource($mission));
    }

    public function validateCompletion(Request $request, Mission $mission): JsonResponse
    {
        $mission = $this->workflow->validateCompletion(
            $mission,
            (new ActorProfile($request->user()))->client(),
        );

        return ApiResponse::resource(new MissionResource($mission->load(['escrowLedger', 'provider'])));
    }

    public function closeWarranty(Request $request, Mission $mission): JsonResponse
    {
        $mission = $this->workflow->closeWarranty(
            $mission,
            (new ActorProfile($request->user()))->client(),
        );

        return ApiResponse::resource(new MissionResource($mission->load('escrowLedger')));
    }

    private function ensureClientOwnsMission(Request $request, Mission $mission): void
    {
        $ownsMission = Client::query()
            ->where('security_account_id', $request->user()->id)
            ->whereKey($mission->client_id)
            ->exists();

        abort_unless($ownsMission, 403, 'Only the mission client can perform this action.');
    }
}
