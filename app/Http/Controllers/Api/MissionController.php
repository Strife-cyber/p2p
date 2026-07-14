<?php

namespace App\Http\Controllers\Api;

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

class MissionController extends Controller
{
    public function __construct(
        private MissionWorkflowService $workflow,
        private EscrowService $escrow,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $actor = new ActorProfile($request->user());
        $query = Mission::query()->with(['serviceCategory', 'client', 'provider', 'escrowLedger']);

        if ($client = $actor->clientOrNull()) {
            $query->where('client_id', $client->id);
        } elseif ($provider = $actor->providerOrNull()) {
            $query->where(function ($builder) use ($provider): void {
                $builder->where('provider_id', $provider->id)
                    ->orWhere('lifecycle_status', 'published');
            });
        } else {
            return MissionResource::collection(collect());
        }

        if ($request->filled('lifecycle_status')) {
            $query->where('lifecycle_status', $request->string('lifecycle_status')->toString());
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
            $request->string('payment_reference')->toString(),
        );

        return ApiResponse::createdResource(new EscrowLedgerResource($ledger));
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
