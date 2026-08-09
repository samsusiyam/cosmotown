<?php

namespace RtRaselBD\Cosmotown\Http;

class Client
{
    public $api_key;
    public $client;
    public $base_url;

    public function __construct($api_key, $base_url)
    {
        $this->api_key = $api_key;
        $this->base_url = rtrim($base_url, '/') . '/';
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->base_url,
        ]);
    }

    public function get($uri, $queryParams = [])
    {
        return $this->client->get($uri, [
            'query' => $queryParams,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-TOKEN' => $this->api_key,
            ],
        ]);
    }

    public function post($uri, $data)
    {
        return $this->client->post($uri, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-TOKEN' => $this->api_key,
            ],
            'json' => $data,
        ]);
    }
}
