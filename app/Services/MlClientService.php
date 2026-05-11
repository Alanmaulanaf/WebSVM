<?php
namespace App\Services;

use GuzzleHttp\Client;

class MlClientService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('services.ml.base_uri'),
            'timeout'  => 5.0,
        ]);
    }

    public function predict(array $payload): array
    {
        $res = $this->http->post('/predict', [
            'json' => $payload,
            'headers' => ['Accept' => 'application/json'],
        ]);
        return json_decode($res->getBody()->getContents(), true);
    }
}
