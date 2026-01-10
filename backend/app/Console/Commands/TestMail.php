<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email=test@example.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un email de test via MailHog';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        try {
            // Forcer l'utilisation de SMTP pour MailHog
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => env('MAIL_HOST', 'mailhog'),
                'mail.mailers.smtp.port' => (int) env('MAIL_PORT', 1025),
                'mail.mailers.smtp.username' => env('MAIL_USERNAME'),
                'mail.mailers.smtp.password' => env('MAIL_PASSWORD'),
                'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION'),
            ]);

            Mail::raw('Ceci est un email de test depuis Best Time. MailHog fonctionne correctement !', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test MailHog - Best Time');
            });

            $this->info("✅ Email envoyé avec succès à {$email}");
            $this->info("📧 Consultez MailHog : http://localhost:9025");
            $this->info("⚙️  Configuration: " . config('mail.mailers.smtp.host') . ":" . config('mail.mailers.smtp.port'));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'envoi : " . $e->getMessage());
            $this->error("Debug - Host: " . env('MAIL_HOST', 'non défini'));
            $this->error("Debug - Port: " . env('MAIL_PORT', 'non défini'));
            return Command::FAILURE;
        }
    }
}
