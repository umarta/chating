<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateFirebaseCredentials extends Command
{
    protected $signature = 'firebase:credentials';
    protected $description = 'Generate Firebase credentials JSON file from environment variables';

    public function handle()
    {
        $credentials = [
            'type' => 'service_account',
            'project_id' => config('services.firebase.project_id'),
            'private_key_id' => config('services.firebase.private_key_id'),
            'private_key' => str_replace('\\n', "\n", config('services.firebase.private_key')),
            'client_email' => config('services.firebase.client_email'),
            'client_id' => config('services.firebase.client_id'),
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_x509_cert_url' => config('services.firebase.client_cert_url'),
        ];

        Storage::put('firebase-credentials.json', json_encode($credentials, JSON_PRETTY_PRINT));
        $this->info('Firebase credentials file generated successfully.');
    }
}
