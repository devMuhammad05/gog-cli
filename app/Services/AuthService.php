<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;

class AuthService
{
    protected Client $client;
    protected string $tokenPath;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setScopes(config('google.scopes'));
        $this->client->setClientId(config('google.client_id'));
        $this->client->setClientSecret(config('google.client_secret'));
        $this->client->setRedirectUri(config('google.redirect_uri'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        $this->tokenPath = config('google.token_path');
        $this->ensureDirectoryExists();
    }

    public function isAuthenticated(): bool
    {
        if (!file_exists($this->tokenPath)) {
            return false;
        }

        $accessToken = json_decode(file_get_contents($this->tokenPath), true);
        $this->client->setAccessToken($accessToken);

        // Check if token is expired and try to refresh
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                $this->saveToken($this->client->getAccessToken());
                return true;
            }
            return false;
        }

        return true;
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate(string $authCode): bool
    {
        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

            if (array_key_exists('error', $accessToken)) {
                throw new \Exception($accessToken['error_description'] ?? 'Authentication failed');
            }

            $this->saveToken($accessToken);
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function logout(): bool
    {
        if (file_exists($this->tokenPath)) {
            return unlink($this->tokenPath);
        }
        return true;
    }

    public function getClient(): Client
    {
        if (!$this->isAuthenticated()) {
            throw new \Exception('Not authenticated. Please run: gog auth:login');
        }

        $accessToken = json_decode(file_get_contents($this->tokenPath), true);
        $this->client->setAccessToken($accessToken);

        return $this->client;
    }

    protected function saveToken(array $accessToken): void
    {
        file_put_contents($this->tokenPath, json_encode($accessToken));
        chmod($this->tokenPath, 0600); // Secure the token file
    }

    public function getToken(): ?array
    {
        return $this->client->getAccessToken();
    }

    protected function ensureDirectoryExists(): void
    {
        $dir = dirname($this->tokenPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
    }
}
