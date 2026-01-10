<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create organizations
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

        // Create projects
        $project1 = Project::create([
            'name' => 'Construction Immeuble Résidentiel',
            'client' => 'Promoteur Immobilier ABC',
            'status' => 'active',
        ]);

        $project2 = Project::create([
            'name' => 'Rénovation Façade',
            'client' => 'Syndic Copropriété',
            'status' => 'active',
        ]);

        $project3 = Project::create([
            'name' => 'Maintenance Machines Production',
            'client' => 'Industrie Métallurgique',
            'status' => 'active',
        ]);

        $project4 = Project::create([
            'name' => 'Installation Système Électrique',
            'client' => 'Bureau d\'Architecture',
            'status' => 'active',
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
    }
}
