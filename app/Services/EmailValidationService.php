<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EmailValidationService
{
    protected $apiKey;
    protected $url;

    public function __construct()
    {
        $this->apiKey = config('services.email_validation.key');
        $this->url = config('services.email_validation.url');
    }

    public function validate(string $email): array
    {
        $response = Http::get($this->url, [
            'email' => $email,
            'api_key' => $this->apiKey,
        ]);

        return $response->json();
    }
}
