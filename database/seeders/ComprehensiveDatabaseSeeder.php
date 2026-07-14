<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\AnomalyType;
use App\Enums\BadgeType;
use App\Enums\ClientType;
use App\Enums\DisputeStatus;
use App\Enums\EscrowStatus;
use App\Enums\ExecutionMode;
use App\Enums\FlowType;
use App\Enums\LifecycleStatus;
use App\Enums\TransactionType;
use App\Enums\UrgencyLevel;
use App\Enums\ValidationResult;
use App\Models\Client;
use App\Models\ClientReliabilityEvent;
use App\Models\Dispute;
use App\Models\EscrowLedger;
use App\Models\Evaluation;
use App\Models\FinancialTransaction;
use App\Models\Mission;
use App\Models\MissionApplication;
use App\Models\MissionFieldVerification;
use App\Models\ProofFile;
use App\Models\ProofValidation;
use App\Models\Provider;
use App\Models\ProviderTracking;
use App\Models\SecurityAccount;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Support\PhoneNumber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ComprehensiveDatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the database with comprehensive demo data.
     *
     * Order respects foreign-key dependencies:
     *   categories → users → security_accounts → clients/providers → wallets → missions → supporting data
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // ====================================================================
        // PHASE 1 — Service Categories (reference / lookup data)
        // ====================================================================
        // These are the "catégories de missions" the frontend was missing.

        $categories = [];
        $categoryData = [
            ['name' => 'Plumbing',             'description' => 'Water systems, pipe repairs, leak detection, and faucet installation.'],
            ['name' => 'Electrical',           'description' => 'Wiring, panels, electrical safety inspections, and appliance installation.'],
            ['name' => 'HVAC',                 'description' => 'Heating, ventilation, and air conditioning installation, maintenance, and repair.'],
            ['name' => 'Carpentry',            'description' => 'Woodwork, framing, custom furniture, and cabinet installation.'],
            ['name' => 'Painting',             'description' => 'Interior and exterior surface preparation and painting.'],
            ['name' => 'Cleaning',             'description' => 'Residential and commercial deep cleaning, sanitization, and janitorial services.'],
            ['name' => 'Gardening',            'description' => 'Landscaping, lawn maintenance, tree trimming, and irrigation.'],
            ['name' => 'Moving',               'description' => 'Furniture moving, relocation assistance, and packing services.'],
            ['name' => 'IT Support',           'description' => 'Computer repair, network setup, software installation, and tech support.'],
            ['name' => 'Tutoring',             'description' => 'Academic support, language lessons, and test preparation for all levels.'],
            ['name' => 'Masonry',              'description' => 'Bricklaying, concrete work, tiling, and stone installation.'],
            ['name' => 'Welding',              'description' => 'Metal fabrication, welding repairs, gate and railing installation.'],
        ];

        foreach ($categoryData as $data) {
            $categories[$data['name']] = ServiceCategory::create($data);
        }

        $this->command->info('✓ Created '.count($categories).' service categories');

        // ====================================================================
        // PHASE 2 — Users + Security Accounts + Roles
        // ====================================================================

        // ─── Admin ──────────────────────────────────────────────────────────

        $adminUser = User::create([
            'name'     => 'Admin P2P',
            'phone'    => PhoneNumber::normalize('+237670000001'),
            'email'    => 'admin@p2p.test',
            'password' => $password,
        ]);

        SecurityAccount::create([
            'user_id'            => $adminUser->id,
            'real_phone'         => $adminUser->phone,
            'proxy_number'       => PhoneNumber::normalize('+237671000001'),
            'device_fingerprint' => hash('sha256', 'admin-device-001'),
            'national_id_hash'   => hash('sha256', 'admin-nid-001'),
        ]);

        // ─── Clients ────────────────────────────────────────────────────────

        $clientData = [
            ['name' => 'Alice Ndongo',   'phone' => '+237670000002', 'type' => ClientType::Individual],
            ['name' => 'Bob Kamga',      'phone' => '+237670000003', 'type' => ClientType::Individual],
            ['name' => 'Sophie\'s Boutique', 'phone' => '+237670000004', 'type' => ClientType::Business],
        ];

        $clients = [];
        foreach ($clientData as $i => $data) {
            $idx = $i + 2;
            $user = User::create([
                'name'     => $data['name'],
                'phone'    => PhoneNumber::normalize($data['phone']),
                'email'    => 'client'.$idx.'@p2p.test',
                'password' => $password,
            ]);

            SecurityAccount::create([
                'user_id'            => $user->id,
                'real_phone'         => $user->phone,
                'proxy_number'       => PhoneNumber::normalize('+2376710000'.str_pad((string) $idx, 2, '0', STR_PAD_LEFT)),
                'device_fingerprint' => hash('sha256', 'client-device-'.$user->id),
                'national_id_hash'   => hash('sha256', 'client-nid-'.$user->id),
            ]);

            $clients[$data['name']] = Client::create([
                'security_account_id' => $user->id,
                'client_type'         => $data['type']->value,
            ]);
        }

        $this->command->info('✓ Created '.count($clients).' clients');

        // ─── Providers ──────────────────────────────────────────────────────

        $providerData = [
            ['name' => 'David Nkwi',       'phone' => '+237670000005', 'badge' => BadgeType::Gold,   'srt' => 92.5, 'missions_wo_dispute' => 48],
            ['name' => 'Grace Tchinda',    'phone' => '+237670000006', 'badge' => BadgeType::Green,  'srt' => 78.3, 'missions_wo_dispute' => 22],
            ['name' => 'Henri Mbida',      'phone' => '+237670000007', 'badge' => BadgeType::Blue,   'srt' => 65.1, 'missions_wo_dispute' => 12],
            ['name' => 'Isabelle Eyanga',  'phone' => '+237670000008', 'badge' => BadgeType::Grey,   'srt' => 45.0, 'missions_wo_dispute' => 3],
            ['name' => 'Jean-Pierre Atangana', 'phone' => '+237670000009', 'badge' => BadgeType::Blue,   'srt' => 60.8, 'missions_wo_dispute' => 8],
        ];

        $providers = [];
        foreach ($providerData as $i => $data) {
            $idx = $i + 5;
            $user = User::create([
                'name'     => $data['name'],
                'phone'    => PhoneNumber::normalize($data['phone']),
                'email'    => 'provider'.$idx.'@p2p.test',
                'password' => $password,
            ]);

            SecurityAccount::create([
                'user_id'            => $user->id,
                'real_phone'         => $user->phone,
                'proxy_number'       => PhoneNumber::normalize('+2376710000'.str_pad((string) $idx, 2, '0', STR_PAD_LEFT)),
                'device_fingerprint' => hash('sha256', 'provider-device-'.$user->id),
                'national_id_hash'   => hash('sha256', 'provider-nid-'.$user->id),
            ]);

            $providers[$data['name']] = Provider::create([
                'security_account_id'            => $user->id,
                'current_badge'                  => $data['badge']->value,
                'badge_modified_at'              => now()->subDays(rand(30, 120)),
                'badge_expires_at'               => now()->addMonths(rand(3, 12)),
                'srt_score'                      => $data['srt'],
                'missions_without_dispute_count' => $data['missions_wo_dispute'],
                'activity_status'                 => ActivityStatus::Available->value,
            ]);
        }

        // ─── Arbitrator (regular user who can arbitrate disputes) ──────────

        $arbitratorUser = User::create([
            'name'     => 'Mme Yvette Mbah',
            'phone'    => PhoneNumber::normalize('+237670000010'),
            'email'    => 'arbitrator@p2p.test',
            'password' => $password,
        ]);

        SecurityAccount::create([
            'user_id'            => $arbitratorUser->id,
            'real_phone'         => $arbitratorUser->phone,
            'proxy_number'       => PhoneNumber::normalize('+23767100010'),
            'device_fingerprint' => hash('sha256', 'arbitrator-device-001'),
            'national_id_hash'   => hash('sha256', 'arbitrator-nid-001'),
        ]);

        $this->command->info('✓ Created '.count($providers).' providers + 1 arbitrator');

        // ====================================================================
        // PHASE 3 — Wallets (one per user)
        // ====================================================================

        $walletBalances = [
            $adminUser->id     => 0,
            // Clients
            $clients['Alice Ndongo']->securityAccount->user_id    => 50000,
            $clients['Bob Kamga']->securityAccount->user_id       => 35000,
            $clients['Sophie\'s Boutique']->securityAccount->user_id => 120000,
            // Providers
            $providers['David Nkwi']->securityAccount->user_id       => 185000,
            $providers['Grace Tchinda']->securityAccount->user_id    => 95000,
            $providers['Henri Mbida']->securityAccount->user_id      => 42000,
            $providers['Isabelle Eyanga']->securityAccount->user_id  => 15000,
            $providers['Jean-Pierre Atangana']->securityAccount->user_id => 31000,
            // Arbitrator
            $arbitratorUser->id => 0,
        ];

        $wallets = [];
        foreach ($walletBalances as $userId => $balance) {
            $wallets[$userId] = Wallet::create([
                'user_id'         => $userId,
                'current_balance' => $balance,
            ]);
        }

        $this->command->info('✓ Created '.count($wallets).' wallets');

        // ====================================================================
        // PHASE 4 — Provider Skills (attach categories to providers)
        // ====================================================================

        $providerSkills = [
            'David Nkwi'       => ['Plumbing', 'Electrical', 'HVAC', 'Welding'],
            'Grace Tchinda'    => ['Electrical', 'Carpentry', 'IT Support'],
            'Henri Mbida'      => ['Painting', 'Masonry', 'Plumbing'],
            'Isabelle Eyanga'  => ['Cleaning', 'Gardening'],
            'Jean-Pierre Atangana' => ['Gardening', 'Moving', 'Carpentry'],
        ];

        foreach ($providerSkills as $providerName => $catNames) {
            $providers[$providerName]->serviceCategories()->attach(
                collect($catNames)->map(fn ($name) => $categories[$name]->id)
            );
        }

        $this->command->info('✓ Attached skills to providers');

        // ====================================================================
        // PHASE 5 — Missions (every lifecycle status)
        // ====================================================================
        // IMPORTANT: The partial unique index `uq_active_mission_provider` allows
        // only ONE non-deleted mission per provider with status 'assigned' or
        // 'in_progress'. All other statuses are free.

        $alice = $clients['Alice Ndongo'];
        $bob   = $clients['Bob Kamga'];
        $sophie = $clients['Sophie\'s Boutique'];

        $david    = $providers['David Nkwi'];
        $grace    = $providers['Grace Tchinda'];
        $henri    = $providers['Henri Mbida'];
        $isabelle = $providers['Isabelle Eyanga'];
        $jp       = $providers['Jean-Pierre Atangana'];

        $now = now();

        // Helper: create a mission with its escrow ledger in one call
        $createMission = function (
            Client $client,
            ?Provider $provider,
            ServiceCategory $category,
            string $title,
            string $address,
            LifecycleStatus $status,
            UrgencyLevel $urgency,
            ExecutionMode $mode,
            float $estimatedPrice,
            ?float $finalPrice = null,
            ?string $scheduledAt = null,
            ?string $completedAt = null,
            ?string $warrantyExpiresAt = null,
        ) use ($now): Mission {
            $mission = Mission::create([
                'client_id'            => $client->id,
                'provider_id'          => $provider?->id,
                'service_category_id'  => $category->id,
                'title'                => $title,
                'description'          => 'Détails de la mission à discuter avec le prestataire.',
                'intervention_address' => $address,
                'estimated_price'      => $estimatedPrice,
                'final_price'          => $finalPrice,
                'urgency_level'        => $urgency->value,
                'execution_mode'       => $mode->value,
                'lifecycle_status'     => $status->value,
                'pairing_code'         => strtoupper(Str::random(8)),
                'scheduled_at'         => $scheduledAt ? new \DateTimeImmutable($scheduledAt) : $now->addDays(rand(1, 14)),
                'completed_at'         => $completedAt ? new \DateTimeImmutable($completedAt) : null,
                'warranty_expires_at'  => $warrantyExpiresAt ? new \DateTimeImmutable($warrantyExpiresAt) : null,
            ]);

            // Create escrow for every mission that has a defined price
            $escrowAmount = $finalPrice ?? $estimatedPrice;
            EscrowLedger::create([
                'mission_id'           => $mission->id,
                'total_amount'         => $escrowAmount,
                'escrow_status'        => EscrowStatus::Blocked->value,
                'transaction_reference' => 'TXN-'.strtoupper(Str::random(12)),
                'locked_at'            => $now,
                'released_at'          => null,
            ]);

            return $mission;
        };

        // ────────────────────────────────────────────────────────────────────
        // 1. Published (open for applications)
        // ────────────────────────────────────────────────────────────────────

        $publishedMissions = [];
        $pubData = [
            [$alice, $categories['Moving'],     'Déménagement bureau vers entrepôt',       "15 Rue Marchand, Bonanjo, Douala",                250000, UrgencyLevel::Normal],
            [$bob,   $categories['IT Support'],  'Installation réseau pour boutique',      "42 Avenue Kennedy, Ekounou, Yaoundé",              85000, UrgencyLevel::High],
            [$sophie, $categories['Tutoring'],   'Cours d\'anglais des affaires',          "Quartier Ngoa-Ekélé, Yaoundé",                     50000, UrgencyLevel::Normal],
            [$alice, $categories['Plumbing'],    'Réparation chauffe-eau',                 "Rue 2041, Bonapriso, Douala",                     120000, UrgencyLevel::Critical],
        ];

        foreach ($pubData as $d) {
            $publishedMissions[] = $createMission(
                $d[0], null, $d[1], $d[2], $d[3],
                LifecycleStatus::Published, $d[5], ExecutionMode::Classic, $d[4]
            );
        }

        // ────────────────────────────────────────────────────────────────────
        // 2. Assigned
        // ────────────────────────────────────────────────────────────────────

        $assignedMission = $createMission(
            $alice, $david, $categories['Plumbing'],
            'Fuite d\'eau salle de bain principale',
            "Villa 23, Rue des Palmiers, Bonanjo, Douala",
            LifecycleStatus::Assigned, UrgencyLevel::High, ExecutionMode::Classic,
            95000
        );

        // Applications on the assigned mission (other providers who applied)
        MissionApplication::create([
            'mission_id'  => $assignedMission->id,
            'provider_id' => $grace->id,
            'status'      => 'rejected',
        ]);
        MissionApplication::create([
            'mission_id'  => $assignedMission->id,
            'provider_id' => $henri->id,
            'status'      => 'rejected',
        ]);

        // Field verification for assigned mission (theoretical only)
        MissionFieldVerification::create([
            'mission_id'            => $assignedMission->id,
            'theoretical_latitude'  => 4.04691,
            'theoretical_longitude' => 9.70898,
            'checkin_latitude'      => null,
            'checkin_longitude'     => null,
            'checked_in_at'         => null,
        ]);

        // ────────────────────────────────────────────────────────────────────
        // 3. Check-in In Progress
        // ────────────────────────────────────────────────────────────────────

        $checkinMission = $createMission(
            $bob, $henri, $categories['Electrical'],
            'Réparation prise électrique',
            "Appartement 8, Immeuble Fouda, Bastos, Yaoundé",
            LifecycleStatus::CheckInInProgress, UrgencyLevel::Normal, ExecutionMode::Classic,
            35000
        );

        MissionFieldVerification::create([
            'mission_id'            => $checkinMission->id,
            'theoretical_latitude'  => 3.86667,
            'theoretical_longitude' => 11.51667,
            'checkin_latitude'      => 3.86691,
            'checkin_longitude'     => 11.51720,
            'checked_in_at'         => $now->copy()->subMinutes(15),
        ]);

        // ────────────────────────────────────────────────────────────────────
        // 4. In Progress
        // ────────────────────────────────────────────────────────────────────

        $inProgressMission = $createMission(
            $alice, $grace, $categories['Electrical'],
            'Mise aux normes du tableau électrique',
            "Maison 45, Rue 3053, Mvog-Mbi, Yaoundé",
            LifecycleStatus::InProgress, UrgencyLevel::High, ExecutionMode::Vip,
            200000
        );

        MissionFieldVerification::create([
            'mission_id'            => $inProgressMission->id,
            'theoretical_latitude'  => 3.89011,
            'theoretical_longitude' => 11.48920,
            'checkin_latitude'      => 3.89015,
            'checkin_longitude'     => 11.48922,
            'checked_in_at'         => $now->copy()->subHours(2),
        ]);

        // Proof files submitted during work
        $proofs = [];
        foreach (['tableau_avant.jpg', 'cablage_etape1.jpg', 'tableau_apres.jpg'] as $file) {
            $proof = ProofFile::create([
                'mission_id'  => $inProgressMission->id,
                'storage_url' => 'https://storage.p2p.cm/proofs/'.$inProgressMission->id.'/'.$file,
                'captured_at' => $now->copy()->subHours(rand(1, 3)),
            ]);
            $proofs[] = $proof;
        }

        // Some proofs auto-validated
        ProofValidation::create([
            'proof_file_id'     => $proofs[0]->id,
            'flow_type'         => FlowType::PhotoBefore->value,
            'validation_result' => ValidationResult::AutoValidated->value,
            'validator_id'      => $adminUser->id,
            'created_at'        => $now,
        ]);
        ProofValidation::create([
            'proof_file_id'     => $proofs[2]->id,
            'flow_type'         => FlowType::PhotoAfter->value,
            'validation_result' => ValidationResult::AutoValidated->value,
            'validator_id'      => $adminUser->id,
            'created_at'        => $now,
        ]);

        // ────────────────────────────────────────────────────────────────────
        // 5. Completed (× 2)
        // ────────────────────────────────────────────────────────────────────

        $completed1 = $createMission(
            $bob, $david, $categories['HVAC'],
            'Installation climatisation bureau',
            "Bureau 304, Akwa Center, Douala",
            LifecycleStatus::Completed, UrgencyLevel::Normal, ExecutionMode::Classic,
            450000, 420000,
            $now->copy()->subDays(12)->format('Y-m-d H:i:s'),
            $now->copy()->subDays(8)->format('Y-m-d H:i:s'),
            $now->copy()->subDays(8)->addMonths(6)->format('Y-m-d H:i:s'),
        );

        // Update escrow for the completed mission
        $completed1->escrowLedger->update([
            'escrow_status' => EscrowStatus::ThirtyPercentReleased->value,
            'released_at'   => $now->copy()->subDays(8),
        ]);

        MissionFieldVerification::create([
            'mission_id'            => $completed1->id,
            'theoretical_latitude'  => 4.04520,
            'theoretical_longitude' => 9.69610,
            'checkin_latitude'      => 4.04525,
            'checkin_longitude'     => 9.69608,
            'checked_in_at'         => $now->copy()->subDays(12),
        ]);

        // Proof files for completed mission
        foreach (['instal_avant.jpg', 'instal_pendant.jpg', 'instal_final.jpg'] as $file) {
            $pf = ProofFile::create([
                'mission_id'  => $completed1->id,
                'storage_url' => 'https://storage.p2p.cm/proofs/'.$completed1->id.'/'.$file,
                'captured_at' => $now->copy()->subDays(rand(10, 12)),
            ]);
            ProofValidation::create([
                'proof_file_id'     => $pf->id,
                'flow_type'         => FlowType::PhotoAfter->value,
                'validation_result' => ValidationResult::ExpertValidated->value,
                'validator_id'      => $adminUser->id,
                'created_at'        => $now,
            ]);
        }

        Evaluation::create([
            'mission_id' => $completed1->id,
            'rating'     => 5,
            'comment'    => 'Travail impeccable et rapide. Je recommande David !',
            'created_at' => $now->copy()->subDays(7),
        ]);

        // ── Second completed mission ──

        $completed2 = $createMission(
            $bob, $grace, $categories['Carpentry'],
            'Fabrication bibliothèque sur mesure',
            "Domicile 12, Rue 1023, Mvog-Mbi, Yaoundé",
            LifecycleStatus::Completed, UrgencyLevel::Normal, ExecutionMode::Classic,
            180000, 180000,
            $now->copy()->subDays(20)->format('Y-m-d H:i:s'),
            $now->copy()->subDays(15)->format('Y-m-d H:i:s'),
            $now->copy()->subDays(15)->addMonths(6)->format('Y-m-d H:i:s'),
        );

        $completed2->escrowLedger->update([
            'escrow_status' => EscrowStatus::ThirtyPercentReleased->value,
            'released_at'   => $now->copy()->subDays(15),
        ]);

        MissionFieldVerification::create([
            'mission_id'            => $completed2->id,
            'theoretical_latitude'  => 3.88450,
            'theoretical_longitude' => 11.49330,
            'checkin_latitude'      => 3.88455,
            'checkin_longitude'     => 11.49332,
            'checked_in_at'         => $now->copy()->subDays(20),
        ]);

        Evaluation::create([
            'mission_id' => $completed2->id,
            'rating'     => 4,
            'comment'    => 'Belle bibliothèque, quelques finitions à revoir mais satisfaisant.',
            'created_at' => $now->copy()->subDays(14),
        ]);

        // ────────────────────────────────────────────────────────────────────
        // 6. Under Warranty
        // ────────────────────────────────────────────────────────────────────

        $warrantyMission = $createMission(
            $sophie, $henri, $categories['Painting'],
            'Peinture intérieure boutique',
            "Boutique 7, Centre Commercial Melen, Yaoundé",
            LifecycleStatus::UnderWarranty, UrgencyLevel::Normal, ExecutionMode::Classic,
            250000, 235000,
            $now->copy()->subDays(45)->format('Y-m-d H:i:s'),
            $now->copy()->subDays(40)->format('Y-m-d H:i:s'),
            $now->copy()->addMonths(5)->format('Y-m-d H:i:s'),   // warranty still active
        );

        $warrantyMission->escrowLedger->update([
            'escrow_status' => EscrowStatus::ThirtyPercentReleased->value,
            'released_at'   => $now->copy()->subDays(40),
        ]);

        MissionFieldVerification::create([
            'mission_id'            => $warrantyMission->id,
            'theoretical_latitude'  => 3.86150,
            'theoretical_longitude' => 11.50280,
            'checkin_latitude'      => 3.86148,
            'checkin_longitude'     => 11.50285,
            'checked_in_at'         => $now->copy()->subDays(45),
        ]);

        Evaluation::create([
            'mission_id' => $warrantyMission->id,
            'rating'     => 4,
            'comment'    => 'Belle peinture, couleurs fidèles au choix.',
            'created_at' => $now->copy()->subDays(39),
        ]);

        // ────────────────────────────────────────────────────────────────────
        // 7. Closed (warranty expired)
        // ────────────────────────────────────────────────────────────────────

        $closedMission = $createMission(
            $alice, $isabelle, $categories['Cleaning'],
            'Nettoyage en profondeur studio',
            "Studio 5, Résidence Mokolo, Douala",
            LifecycleStatus::Closed, UrgencyLevel::Normal, ExecutionMode::Classic,
            40000, 40000,
            $now->copy()->subDays(200)->format('Y-m-d H:i:s'),
            $now->copy()->subDays(198)->format('Y-m-d H:i:s'),
            $now->copy()->subDays(198)->addMonths(3)->format('Y-m-d H:i:s'),
        );

        // Warranty expired 3 months ago — mission is closed
        // Override warranty to be in the past
        $closedMission->updateQuietly(['warranty_expires_at' => $now->copy()->subMonths(3)]);

        $closedMission->escrowLedger->update([
            'escrow_status' => EscrowStatus::ThirtyPercentReleased->value,
            'released_at'   => $now->copy()->subDays(198),
        ]);

        MissionFieldVerification::create([
            'mission_id'            => $closedMission->id,
            'theoretical_latitude'  => 4.05090,
            'theoretical_longitude' => 9.71050,
            'checkin_latitude'      => 4.05092,
            'checkin_longitude'     => 9.71048,
            'checked_in_at'         => $now->copy()->subDays(200),
        ]);

        Evaluation::create([
            'mission_id' => $closedMission->id,
            'rating'     => 4,
            'comment'    => 'Studio impeccable, très professionnelle.',
            'created_at' => $now->copy()->subDays(197),
        ]);

        // ────────────────────────────────────────────────────────────────────
        // 8. In Dispute
        // ────────────────────────────────────────────────────────────────────

        $disputeMission = $createMission(
            $sophie, $jp, $categories['Gardening'],
            'Aménagement jardin boutique',
            "Résidence Les Oliviers, Odza, Yaoundé",
            LifecycleStatus::InDispute, UrgencyLevel::Normal, ExecutionMode::Classic,
            150000, 150000,
            $now->copy()->subDays(10)->format('Y-m-d H:i:s'),
            null, null,
        );

        MissionFieldVerification::create([
            'mission_id'            => $disputeMission->id,
            'theoretical_latitude'  => 3.85210,
            'theoretical_longitude' => 11.53540,
            'checkin_latitude'      => 3.85215,
            'checkin_longitude'     => 11.53538,
            'checked_in_at'         => $now->copy()->subDays(10),
        ]);

        // Client submitted a proof showing poor workmanship
        $disputeProof = ProofFile::create([
            'mission_id'  => $disputeMission->id,
            'storage_url' => 'https://storage.p2p.cm/proofs/'.$disputeMission->id.'/jardin_abime.jpg',
            'captured_at' => $now->copy()->subDays(8),
        ]);

        ProofValidation::create([
            'proof_file_id'     => $disputeProof->id,
            'flow_type'         => FlowType::PhotoAfter->value,
            'validation_result' => ValidationResult::Rejected->value,
            'validator_id'      => $adminUser->id,
            'created_at'        => $now,
        ]);

        // Open dispute
        Dispute::create([
            'mission_id'      => $disputeMission->id,
            'anomaly_type'    => AnomalyType::QualityDispute->value,
            'dispute_status'  => DisputeStatus::Open->value,
            'arbitrator_id'   => null,
            'decision_notes'  => null,
            'srt_penalty'     => 0,
            'triggered_at'    => $now->copy()->subDays(7),
        ]);

        // ────────────────────────────────────────────────────────────────────
        // Applications on published missions (providers browsing & applying)
        // ────────────────────────────────────────────────────────────────────

        $applicationSets = [
            [$publishedMissions[0], [$david, $grace]],    // Moving: 2 providers
            [$publishedMissions[1], [$henri, $jp]],       // IT Support: 2 providers
            [$publishedMissions[2], [$grace]],             // Tutoring: 1 provider
        ];

        foreach ($applicationSets as [$mission, $applicants]) {
            foreach ($applicants as $provider) {
                MissionApplication::create([
                    'mission_id'  => $mission->id,
                    'provider_id' => $provider->id,
                    'status'      => 'pending',
                ]);
            }
        }

        $this->command->info('✓ Created 12 missions across all lifecycle statuses');

        // ====================================================================
        // PHASE 6 — Provider Tracking (GPS trails for active providers)
        // ====================================================================

        $trackingData = [
            $david->id    => [['lat' => 4.0469, 'lng' => 9.7090], ['lat' => 4.0472, 'lng' => 9.7087], ['lat' => 4.0475, 'lng' => 9.7084]],
            $grace->id    => [['lat' => 3.8901, 'lng' => 11.4892], ['lat' => 3.8903, 'lng' => 11.4895]],
            $henri->id    => [['lat' => 3.8667, 'lng' => 11.5167], ['lat' => 3.8669, 'lng' => 11.5170]],
            $isabelle->id => [['lat' => 4.0509, 'lng' => 9.7105]],
        ];

        foreach ($trackingData as $providerId => $points) {
            foreach ($points as $pt) {
                ProviderTracking::create([
                    'provider_id' => $providerId,
                    'latitude'    => $pt['lat'],
                    'longitude'   => $pt['lng'],
                    'recorded_at' => $now->copy()->subMinutes(rand(5, 60)),
                ]);
            }
        }

        $this->command->info('✓ Created provider tracking points');

        // ====================================================================
        // PHASE 7 — Financial Transactions (wallet history)
        // ====================================================================

        $transactions = [
            // Client wallets — deposits
            [$wallets[$alice->securityAccount->user_id]->id, 100000, TransactionType::Deposit,      'MTN-'.rand(100000, 999999)],
            [$wallets[$alice->securityAccount->user_id]->id, -45000,  TransactionType::MissionPayment, 'PAY-'.$assignedMission->pairing_code],
            [$wallets[$bob->securityAccount->user_id]->id,   50000,  TransactionType::Deposit,      'MTN-'.rand(100000, 999999)],
            [$wallets[$sophie->securityAccount->user_id]->id, 200000, TransactionType::Deposit,     'ORANGE-'.rand(100000, 999999)],
            // Provider wallets — payments received
            [$wallets[$david->securityAccount->user_id]->id,   420000, TransactionType::MissionPayment, 'PAY-'.$completed1->pairing_code],
            [$wallets[$david->securityAccount->user_id]->id,   -50000, TransactionType::Withdrawal,  'WTHD-'.rand(100000, 999999)],
            [$wallets[$grace->securityAccount->user_id]->id,    180000, TransactionType::MissionPayment, 'PAY-'.$completed2->pairing_code],
            [$wallets[$grace->securityAccount->user_id]->id,    -30000, TransactionType::Withdrawal, 'WTHD-'.rand(100000, 999999)],
            [$wallets[$henri->securityAccount->user_id]->id,    235000, TransactionType::MissionPayment, 'PAY-'.$warrantyMission->pairing_code],
            // Platform commissions
            [$wallets[$adminUser->id]->id, 21000, TransactionType::PlatformCommission, 'COM-'.$completed1->pairing_code],
            [$wallets[$adminUser->id]->id, 9000,  TransactionType::PlatformCommission, 'COM-'.$completed2->pairing_code],
        ];

        foreach ($transactions as $tx) {
            FinancialTransaction::create([
                'wallet_id'          => $tx[0],
                'amount'             => $tx[1],
                'transaction_type'   => $tx[2]->value,
                'external_reference' => $tx[3],
                'created_at'         => $now->copy()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('✓ Created financial transactions');

        // ====================================================================
        // PHASE 8 — Client Reliability Events
        // ====================================================================

        $reliabilityEvents = [
            [$alice->id, 'mission_completed', 1.5,  'Mission terminée sans problème'],
            [$alice->id, 'mission_completed', 2.0,  'Mission terminée sans problème'],
            [$bob->id,   'mission_completed', 1.5,  'Mission terminée sans problème'],
            [$bob->id,   'mission_completed', 2.0,  'Mission terminée sans problème'],
            [$sophie->id, 'mission_completed', 2.0,  'Mission terminée sans problème'],
            [$sophie->id, 'late_cancellation', -3.0, 'Annulation tardive d\'une mission de jardinage'],
        ];

        foreach ($reliabilityEvents as $ev) {
            ClientReliabilityEvent::create([
                'client_id'    => $ev[0],
                'event_type'   => $ev[1],
                'score_impact' => $ev[2],
                'description'  => $ev[3],
                'created_at'   => $now->copy()->subDays(rand(5, 90)),
            ]);
        }

        $this->command->info('✓ Created client reliability events');

        // ====================================================================
        // SUMMARY
        // ====================================================================

        $this->command->info('── Comprehensive seeds applied ──');
        $this->command->info('Categories: '.ServiceCategory::count());
        $this->command->info('Users:      '.User::count());
        $this->command->info('Clients:    '.Client::count());
        $this->command->info('Providers:  '.Provider::count());
        $this->command->info('Wallets:    '.Wallet::count());
        $this->command->info('Missions:   '.Mission::count());
        $this->command->info('Escrows:    '.EscrowLedger::count());
        $this->command->info('Evals:      '.Evaluation::count());
        $this->command->info('Disputes:   '.Dispute::count());
        $this->command->info('Apps:       '.MissionApplication::count());
        $this->command->info('Transacts:  '.FinancialTransaction::count());
        $this->command->info('────────────────────────────────');
    }
}