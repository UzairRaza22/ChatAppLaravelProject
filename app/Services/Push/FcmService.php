<?php

namespace App\Services\Push;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected $projectId;
    protected $client;
    protected $serviceAccountPath;

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id');
        $this->serviceAccountPath = config('services.fcm.service_account_path');
        $this->client = new Client();
    }

    protected function getAccessToken()
    {
        return Cache::remember('fcm_access_token', 3300, function () { // tokens last 3600s
            $path = base_path($this->serviceAccountPath);
            if (!file_exists($path)) {
                $path = storage_path('app/firebase/service-account.json');
            }

            if (!file_exists($path)) {
                throw new \Exception("FCM Service account file not found at $path");
            }

            $jsonKey = json_decode(file_get_contents($path), true);

            // if project_id is missing in config, get it from json
            if (!$this->projectId && isset($jsonKey['project_id'])) {
                $this->projectId = $jsonKey['project_id'];
            }

            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new ServiceAccountCredentials($scopes, $jsonKey);

            $token = $credentials->fetchAuthToken();
            return $token['access_token'];
        });
    }

    public function sendToToken($token, $title, $body, $data = [])
    {
        $accessToken = $this->getAccessToken();

        // Ensure Project ID is set
        if (!$this->projectId) {
            // attempt fallback read
            $path = base_path($this->serviceAccountPath);
            if (!file_exists($path)) {
                $path = storage_path('app/firebase/service-account.json');
            }
            if (file_exists($path)) {
                $jsonKey = json_decode(file_get_contents($path), true);
                $this->projectId = $jsonKey['project_id'] ?? null;
            }
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'default_sound' => true,
                    'notification_priority' => 'PRIORITY_HIGH',
                ]
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ]
                ]
            ],
            'webpush' => [
                'headers' => [
                    'Urgency' => 'high',
                ],
                'notification' => [
                    'body' => $body,
                    'title' => $title,
                    'vibrate' => [200, 100, 200],
                    'renotify' => true,
                    'tag' => 'chat-message',
                ]
            ]
        ];

        if (!empty($data)) {
            $stringData = [];
            foreach ($data as $key => $value) {
                $stringData[(string)$key] = (string)$value;
            }
            $message['data'] = $stringData;
        }

        $payload = [
            'message' => $message
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBody = json_decode($response->getBody(), true);
            $errorCode = data_get($responseBody, 'error.details.0.errorCode');
            $errorMessage = data_get($responseBody, 'error.message', '');

            $isInvalid = in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])
                         || strpos($errorMessage, 'valid FCM registration token') !== false;

            if ($isInvalid) {
                throw new \Exception('INVALID_TOKEN');
            }

            Log::error('FCM Send Error: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('FCM Send Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
