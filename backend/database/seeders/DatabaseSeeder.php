<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate tables to allow re-seeding without unique constraint violations
        // We use CASCADE to handle foreign key constraints in PostgreSQL
        DB::statement('TRUNCATE TABLE time_entries CASCADE');
        DB::statement('TRUNCATE TABLE projects CASCADE');
        DB::statement('TRUNCATE TABLE clients CASCADE');
        DB::statement('TRUNCATE TABLE users CASCADE');
        DB::statement('TRUNCATE TABLE organizations CASCADE');

        // Create organizations with geolocation settings
        $org1 = Organization::create([
            'name' => 'Construction ABC',
            'code' => 'ORG-001',
            'description' => 'Entreprise de construction générale',
            'address' => '123 Rue de la Construction',
            'city' => 'Bruxelles',
            'postal_code' => '1000',
            'country' => 'Belgique',
            'phone' => '+32 2 123 45 67',
            'email' => 'contact@construction-abc.be',
            'location_required' => true,
            'geofencing_enabled' => true,
            'geofencing_radius' => 100, // 100 meters
        ]);

        $org2 = Organization::create([
            'name' => 'Services Industriels XYZ',
            'code' => 'ORG-002',
            'description' => 'Services d\'entretien et maintenance industrielle',
            'address' => '456 Avenue Industrielle',
            'city' => 'Anvers',
            'postal_code' => '2000',
            'country' => 'Belgique',
            'phone' => '+32 3 987 65 43',
            'email' => 'info@services-xyz.be',
            'location_required' => true,
            'geofencing_enabled' => false,
            'geofencing_radius' => null,
        ]);

        $org3 = Organization::create([
            'name' => 'Développement Logiciel Tech',
            'code' => 'ORG-003',
            'description' => 'Développement d\'applications web et mobiles',
            'address' => '789 Boulevard Digital',
            'city' => 'Gand',
            'postal_code' => '9000',
            'country' => 'Belgique',
            'phone' => '+32 9 456 78 90',
            'email' => 'hello@tech-dev.be',
            'location_required' => false,
            'geofencing_enabled' => false,
            'geofencing_radius' => null,
        ]);

        // Create admin users (can be without organization)
        $admin1 = User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@besttime.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'organization_id' => null,
            'phone' => '+32 2 000 00 01',
            'employee_number' => 'ADM-001',
            'is_active' => true,
        ]);

        $admin2 = User::create([
            'name' => 'Admin Secondaire',
            'email' => 'admin2@besttime.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'organization_id' => null,
            'phone' => '+32 2 000 00 02',
            'employee_number' => 'ADM-002',
            'is_active' => true,
        ]);

        // Create responsables for org1
        $resp1 = User::create([
            'name' => 'Pierre Dubois',
            'email' => 'pierre.dubois@construction-abc.be',
            'password' => Hash::make('password'),
            'role' => 'responsable',
            'organization_id' => $org1->id,
            'phone' => '+32 2 123 45 68',
            'employee_number' => 'RESP-001',
            'hire_date' => now()->subYears(5),
            'is_active' => true,
        ]);

        $resp2 = User::create([
            'name' => 'Marie Martin',
            'email' => 'marie.martin@construction-abc.be',
            'password' => Hash::make('password'),
            'role' => 'responsable',
            'organization_id' => $org1->id,
            'phone' => '+32 2 123 45 69',
            'employee_number' => 'RESP-002',
            'hire_date' => now()->subYears(3),
            'is_active' => true,
        ]);

        // Create responsables for org2
        $resp3 = User::create([
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@services-xyz.be',
            'password' => Hash::make('password'),
            'role' => 'responsable',
            'organization_id' => $org2->id,
            'phone' => '+32 3 987 65 44',
            'employee_number' => 'RESP-003',
            'hire_date' => now()->subYears(7),
            'is_active' => true,
        ]);

        // Create gestionnaires
        $gest1 = User::create([
            'name' => 'Claire Fontaine',
            'email' => 'claire.fontaine@construction-abc.be',
            'password' => Hash::make('password'),
            'role' => 'gestionnaire',
            'organization_id' => $org1->id,
            'phone' => '+32 2 123 45 74',
            'employee_number' => 'GEST-001',
            'hire_date' => now()->subYears(4),
            'is_active' => true,
        ]);

        $gest2 = User::create([
            'name' => 'Paul Mercier',
            'email' => 'paul.mercier@services-xyz.be',
            'password' => Hash::make('password'),
            'role' => 'gestionnaire',
            'organization_id' => $org2->id,
            'phone' => '+32 3 987 65 48',
            'employee_number' => 'GEST-002',
            'hire_date' => now()->subYears(2),
            'is_active' => true,
        ]);

        // Create ouvriers for org1
        $ouvrier1 = User::create([
            'name' => 'Lucas Bernard',
            'email' => 'lucas.bernard@construction-abc.be',
            'password' => Hash::make('password'),
            'role' => 'ouvrier',
            'organization_id' => $org1->id,
            'phone' => '+32 2 123 45 70',
            'employee_number' => 'OUV-001',
            'hire_date' => now()->subYears(2),
            'is_active' => true,
        ]);

        $ouvrier2 = User::create([
            'name' => 'Sophie Lambert',
            'email' => 'sophie.lambert@construction-abc.be',
            'password' => Hash::make('password'),
            'role' => 'ouvrier',
            'organization_id' => $org1->id,
            'phone' => '+32 2 123 45 71',
            'employee_number' => 'OUV-002',
            'hire_date' => now()->subYears(1),
            'is_active' => true,
        ]);

        $ouvrier3 = User::create([
            'name' => 'Thomas Petit',
            'email' => 'thomas.petit@construction-abc.be',
            'password' => Hash::make('password'),
            'role' => 'ouvrier',
            'organization_id' => $org1->id,
            'phone' => '+32 2 123 45 72',
            'employee_number' => 'OUV-003',
            'hire_date' => now()->subMonths(6),
            'is_active' => true,
        ]);

        // Create ouvriers for org2
        $ouvrier4 = User::create([
            'name' => 'Emma Rousseau',
            'email' => 'emma.rousseau@services-xyz.be',
            'password' => Hash::make('password'),
            'role' => 'ouvrier',
            'organization_id' => $org2->id,
            'phone' => '+32 3 987 65 45',
            'employee_number' => 'OUV-004',
            'hire_date' => now()->subYears(4),
            'is_active' => true,
        ]);

        $ouvrier5 = User::create([
            'name' => 'Antoine Moreau',
            'email' => 'antoine.moreau@services-xyz.be',
            'password' => Hash::make('password'),
            'role' => 'ouvrier',
            'organization_id' => $org2->id,
            'phone' => '+32 3 987 65 46',
            'employee_number' => 'OUV-005',
            'hire_date' => now()->subYears(2),
            'is_active' => true,
        ]);

        // Create team leaders (ouvriers avec droits spéciaux)
        $teamLeader1 = User::create([
            'name' => 'Marc Leroy',
            'email' => 'marc.leroy@construction-abc.be',
            'password' => Hash::make('password'),
            'role' => 'team_leader',
            'organization_id' => $org1->id,
            'phone' => '+32 2 123 45 73',
            'employee_number' => 'TL-001',
            'hire_date' => now()->subYears(8),
            'is_active' => true,
        ]);

        $teamLeader2 = User::create([
            'name' => 'Julie Vincent',
            'email' => 'julie.vincent@services-xyz.be',
            'password' => Hash::make('password'),
            'role' => 'team_leader',
            'organization_id' => $org2->id,
            'phone' => '+32 3 987 65 47',
            'employee_number' => 'TL-002',
            'hire_date' => now()->subYears(6),
            'is_active' => true,
        ]);

        // Link ouvriers to responsables (many-to-many)
        // resp1 manages ouvrier1 and ouvrier2
        $resp1->managedOuvriers()->attach([$ouvrier1->id, $ouvrier2->id]);
        
        // resp2 manages ouvrier2 and ouvrier3
        $resp2->managedOuvriers()->attach([$ouvrier2->id, $ouvrier3->id]);
        
        // resp3 manages ouvrier4 and ouvrier5
        $resp3->managedOuvriers()->attach([$ouvrier4->id, $ouvrier5->id]);

        // Link team leaders to ouvriers they can encode for
        // teamLeader1 can encode for ouvrier1 and ouvrier2
        $teamLeader1->teamOuvriers()->attach([$ouvrier1->id, $ouvrier2->id]);
        
        // teamLeader2 can encode for ouvrier4
        $teamLeader2->teamOuvriers()->attach([$ouvrier4->id]);

        // Create clients
        $client1 = \App\Models\Client::create([
            'name' => 'Promoteur Immobilier ABC',
            'contact_person' => 'Jacques Durand',
            'email' => 'contact@promoteur-abc.be',
            'phone' => '+32 2 555 12 34',
            'address' => '15 Avenue des Promoteurs, 1000 Bruxelles',
            'is_active' => true,
        ]);

        $client2 = \App\Models\Client::create([
            'name' => 'Syndic Copropriété Centrale',
            'contact_person' => 'Marie Leblanc',
            'email' => 'syndic@copro-centrale.be',
            'phone' => '+32 2 555 56 78',
            'address' => '42 Rue des Copropriétaires, 1050 Bruxelles',
            'is_active' => true,
        ]);

        $client3 = \App\Models\Client::create([
            'name' => 'Industrie Métallurgique SA',
            'contact_person' => 'Pierre Acier',
            'email' => 'contact@metallurgie-sa.be',
            'phone' => '+32 3 555 90 12',
            'address' => '78 Zone Industrielle, 2000 Anvers',
            'is_active' => true,
        ]);

        $client4 = \App\Models\Client::create([
            'name' => 'Bureau d\'Architecture Moderne',
            'contact_person' => 'Sophie Architecte',
            'email' => 'info@archi-moderne.be',
            'phone' => '+32 9 555 34 56',
            'address' => '23 Boulevard Design, 9000 Gand',
            'is_active' => true,
        ]);

        $client5 = \App\Models\Client::create([
            'name' => 'Ville de Bruxelles',
            'contact_person' => 'Jean Municipal',
            'email' => 'travaux@ville-bruxelles.be',
            'phone' => '+32 2 555 78 90',
            'address' => 'Grand-Place 1, 1000 Bruxelles',
            'is_active' => true,
        ]);

        // Create projects with GPS coordinates, QR codes, and client links
        $project1 = Project::create([
            'name' => 'Construction Immeuble Résidentiel',
            'client_id' => $client1->id,
            'status' => 'active',
            'latitude' => 50.8503,
            'longitude' => 4.3517,
            'geofence_radius' => 100,
            'qr_code_token' => hash('sha256', 'project1-' . now()->timestamp),
        ]);

        $project2 = Project::create([
            'name' => 'Rénovation Façade',
            'client_id' => $client2->id,
            'status' => 'active',
            'latitude' => 50.8467,
            'longitude' => 4.3525,
            'geofence_radius' => 50,
            'qr_code_token' => hash('sha256', 'project2-' . now()->timestamp),
        ]);

        $project3 = Project::create([
            'name' => 'Maintenance Machines Production',
            'client_id' => $client3->id,
            'status' => 'active',
            'latitude' => 51.2194,
            'longitude' => 4.4025,
            'geofence_radius' => 200,
            'qr_code_token' => hash('sha256', 'project3-' . now()->timestamp),
        ]);

        $project4 = Project::create([
            'name' => 'Installation Système Électrique',
            'client_id' => $client4->id,
            'status' => 'active',
            'latitude' => 51.0543,
            'longitude' => 3.7174,
            'geofence_radius' => 75,
            'qr_code_token' => hash('sha256', 'project4-' . now()->timestamp),
        ]);

        $project5 = Project::create([
            'name' => 'Rénovation Voirie Municipale',
            'client_id' => $client5->id,
            'status' => 'active',
            'latitude' => 50.8467,
            'longitude' => 4.3525,
            'geofence_radius' => 150,
            'qr_code_token' => hash('sha256', 'project5-' . now()->timestamp),
        ]);

        // Create time entries for ouvrier1 (encoded by himself)
        TimeEntry::create([
            'user_id' => $ouvrier1->id,
            'encoded_by_user_id' => null, // Encoded by himself
            'project_id' => $project1->id,
            'start_time' => now()->subDays(2)->setTime(7, 0),
            'end_time' => now()->subDays(2)->setTime(15, 30),
            'description' => 'Préparation du chantier et coulage fondations',
            'duration' => 30600, // 8.5 hours
        ]);

        // Create time entry encoded by team leader
        TimeEntry::create([
            'user_id' => $ouvrier1->id,
            'encoded_by_user_id' => $teamLeader1->id, // Encoded by team leader
            'project_id' => $project1->id,
            'start_time' => now()->subDay()->setTime(7, 0),
            'end_time' => now()->subDay()->setTime(16, 0),
            'description' => 'Travaux maçonnerie - encodé par responsable équipe',
            'duration' => 32400, // 9 hours
        ]);

        // Create time entries for ouvrier2
        TimeEntry::create([
            'user_id' => $ouvrier2->id,
            'encoded_by_user_id' => null,
            'project_id' => $project2->id,
            'start_time' => now()->subDays(3)->setTime(8, 0),
            'end_time' => now()->subDays(3)->setTime(17, 0),
            'description' => 'Préparation et ponçage façade',
            'duration' => 32400, // 9 hours
        ]);

        // Create active entry (currently clocked in) for ouvrier3
        TimeEntry::create([
            'user_id' => $ouvrier3->id,
            'encoded_by_user_id' => null,
            'project_id' => $project1->id,
            'start_time' => now()->subHours(3),
            'end_time' => null,
            'description' => 'Travaux en cours - installation échafaudage',
        ]);

        // Create time entries for org2 ouvriers
        TimeEntry::create([
            'user_id' => $ouvrier4->id,
            'encoded_by_user_id' => null,
            'project_id' => $project3->id,
            'start_time' => now()->subDays(4)->setTime(6, 0),
            'end_time' => now()->subDays(4)->setTime(14, 0),
            'description' => 'Révision générale machines',
            'duration' => 28800, // 8 hours
        ]);

        TimeEntry::create([
            'user_id' => $ouvrier5->id,
            'encoded_by_user_id' => null,
            'project_id' => $project4->id,
            'start_time' => now()->subDay()->setTime(8, 30),
            'end_time' => now()->subDay()->setTime(17, 30),
            'description' => 'Installation câblage électrique',
            'duration' => 32400, // 9 hours
        ]);

        // Create time entry encoded by responsable (resp1 encodes for ouvrier1)
        TimeEntry::create([
            'user_id' => $ouvrier1->id,
            'encoded_by_user_id' => $resp1->id, // Encoded by responsable
            'project_id' => $project2->id,
            'start_time' => now()->subHours(5)->setTime(8, 0),
            'end_time' => now()->subHours(5)->setTime(12, 0),
            'description' => 'Encaissement matériaux - encodé par responsable',
            'duration' => 14400, // 4 hours
        ]);

        // Create some entries for responsables (they can also point)
        TimeEntry::create([
            'user_id' => $resp1->id,
            'encoded_by_user_id' => null,
            'project_id' => $project1->id,
            'start_time' => now()->subDays(2)->setTime(9, 0),
            'end_time' => now()->subDays(2)->setTime(12, 0),
            'description' => 'Réunion chantier et vérification planning',
            'duration' => 10800, // 3 hours
        ]);

        // Create entry for admin (they can also point)
        TimeEntry::create([
            'user_id' => $admin1->id,
            'encoded_by_user_id' => null,
            'project_id' => $project1->id,
            'start_time' => now()->subDays(5)->setTime(10, 0),
            'end_time' => now()->subDays(5)->setTime(15, 0),
            'description' => 'Visite contrôle qualité',
            'duration' => 18000, // 5 hours
        ]);

        // ========================================
        // TIME ENTRIES WITH GEOLOCATION & QR CODE
        // ========================================

        // Entry with geolocation (within geofence)
        TimeEntry::create([
            'user_id' => $ouvrier1->id,
            'encoded_by_user_id' => null,
            'project_id' => $project1->id,
            'start_time' => now()->subHours(6),
            'end_time' => now()->subHours(2),
            'description' => 'Travaux avec géolocalisation - dans le périmètre',
            'duration' => 14400, // 4 hours
            'latitude' => 50.8505, // Near project1 location
            'longitude' => 4.3519,
            'location_accuracy' => 15.5,
            'location_captured_at' => now()->subHours(6),
            'qr_code_scanned' => false,
        ]);

        // Entry with QR code scan + geolocation
        TimeEntry::create([
            'user_id' => $ouvrier2->id,
            'encoded_by_user_id' => null,
            'project_id' => $project2->id,
            'start_time' => now()->subHours(4),
            'end_time' => now()->subHours(1),
            'description' => 'Pointage par QR code avec localisation',
            'duration' => 10800, // 3 hours
            'latitude' => 50.8468,
            'longitude' => 4.3526,
            'location_accuracy' => 10.2,
            'location_captured_at' => now()->subHours(4),
            'qr_code_scanned' => true,
        ]);

        // Entry with geolocation but outside geofence (for testing)
        TimeEntry::create([
            'user_id' => $ouvrier3->id,
            'encoded_by_user_id' => null,
            'project_id' => $project3->id,
            'start_time' => now()->subHours(8),
            'end_time' => now()->subHours(4),
            'description' => 'Travaux hors périmètre (test géofencing)',
            'duration' => 14400, // 4 hours
            'latitude' => 51.2500, // Far from project3 location
            'longitude' => 4.5000,
            'location_accuracy' => 25.0,
            'location_captured_at' => now()->subHours(8),
            'qr_code_scanned' => false,
        ]);

        // Active entry with QR code (currently running)
        TimeEntry::create([
            'user_id' => $ouvrier4->id,
            'encoded_by_user_id' => null,
            'project_id' => $project4->id,
            'start_time' => now()->subMinutes(45),
            'end_time' => null,
            'description' => 'Travaux en cours - pointé par QR code',
            'latitude' => 51.0545,
            'longitude' => 3.7176,
            'location_accuracy' => 8.5,
            'location_captured_at' => now()->subMinutes(45),
            'qr_code_scanned' => true,
        ]);

        // Entry with very precise location
        TimeEntry::create([
            'user_id' => $ouvrier5->id,
            'encoded_by_user_id' => null,
            'project_id' => $project1->id,
            'start_time' => now()->subDays(1)->setTime(7, 30),
            'end_time' => now()->subDays(1)->setTime(16, 30),
            'description' => 'Journée complète avec localisation précise',
            'duration' => 32400, // 9 hours
            'latitude' => 50.8504,
            'longitude' => 4.3518,
            'location_accuracy' => 5.0, // Very precise
            'location_captured_at' => now()->subDays(1)->setTime(7, 30),
            'qr_code_scanned' => true,
        ]);

        // Team leader entry with QR code
        TimeEntry::create([
            'user_id' => $teamLeader1->id,
            'encoded_by_user_id' => null,
            'project_id' => $project1->id,
            'start_time' => now()->subHours(5),
            'end_time' => now()->subHours(1),
            'description' => 'Supervision équipe avec pointage QR',
            'duration' => 14400, // 4 hours
            'latitude' => 50.8502,
            'longitude' => 4.3516,
            'location_accuracy' => 12.0,
            'location_captured_at' => now()->subHours(5),
            'qr_code_scanned' => true,
        ]);

        // Responsable entry with location
        TimeEntry::create([
            'user_id' => $resp1->id,
            'encoded_by_user_id' => null,
            'project_id' => $project2->id,
            'start_time' => now()->subHours(3),
            'end_time' => now()->subMinutes(30),
            'description' => 'Inspection chantier avec géolocalisation',
            'duration' => 9000, // 2.5 hours
            'latitude' => 50.8466,
            'longitude' => 4.3524,
            'location_accuracy' => 18.0,
            'location_captured_at' => now()->subHours(3),
            'qr_code_scanned' => false,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 Created:');
        $this->command->info('   - 3 Organizations (with geolocation settings)');
        $this->command->info('   - 14 Users (2 admins, 3 responsables, 2 gestionnaires, 2 team leaders, 5 ouvriers)');
        $this->command->info('   - 5 Clients');
        $this->command->info('   - 5 Projects (with GPS coordinates, QR codes, and client links)');
        $this->command->info('   - ' . TimeEntry::count() . ' Time Entries (including geolocation and QR scans)');
        $this->command->info('');
        $this->command->info('🔐 Test credentials:');
        $this->command->info('   Admin: admin@besttime.test / password');
        $this->command->info('   Responsable: pierre.dubois@construction-abc.be / password');
        $this->command->info('   Gestionnaire: claire.fontaine@construction-abc.be / password');
        $this->command->info('   Team Leader: marc.leroy@construction-abc.be / password');
        $this->command->info('   Ouvrier: lucas.bernard@construction-abc.be / password');
        $this->command->info('');
        $this->command->info('📱 QR Codes generated for all projects!');
        $this->command->info('   Use /api/admin/projects/{id}/qr-code to retrieve them');
    }
}
