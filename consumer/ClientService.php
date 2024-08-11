<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ClientService {
    private $httpClient;
    private $apiUrl;
    private $apiKey;

    public function __construct($apiUrl, $apiKey) {
        $this->httpClient = new Client();
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    private function sendRequest($method, $endpoint, $data = []) {
        $url = $this->apiUrl . $endpoint;
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ];

        try {
            $response = $this->httpClient->request($method, $url, [
                'headers' => $headers,
                'json' => $data
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            echo "HTTP Request failed: " . $e->getMessage() . "\n";
            return null;
        }
    }

    public function create($data) {
        $response = $this->sendRequest('POST', '/api/admin/client/create', $data);
        if ($response) {
            echo "Client created: " . json_encode($response) . "\n";
        }
    }

    public function update($data) {
        $response = $this->sendRequest('PUT', '/api/admin/client/update', $data);
        if ($response) {
            echo "Client updated: " . json_encode($response) . "\n";
        }
    }

    public function delete($data) {
        $response = $this->sendRequest('DELETE', '/api/admin/client/delete', ['id' => $data['id']]);
        if ($response) {
            echo "Client deleted: " . json_encode($response) . "\n";
        }
    }
}
