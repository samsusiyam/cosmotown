<?php

namespace RtRaselBD\Cosmotown\Http;

class Client
{
    public $api_key;
    public $base_url;

    public function __construct($api_key, $base_url)
    {
        $this->api_key = $api_key;
        $this->base_url = rtrim($base_url, '/') . '/';
    }

    private function request($method, $uri, $options = [])
    {
        $url = $this->base_url . $uri;
        if (!empty($options['query'])) {
            $url .= '?' . http_build_query($options['query']);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-API-TOKEN: ' . $this->api_key,
            ],
            CURLOPT_USERAGENT => 'curl/7.61.1',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body'] ?? '');
        }

        $body = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return new \GuzzleHttp\Psr7\Response($statusCode, [], $body ?: $error);
    }

    public function get($uri, $queryParams = [])
    {
        return $this->request('GET', $uri, ['query' => $queryParams]);
    }

    public function post($uri, $data)
    {
        return $this->request('POST', $uri, ['body' => json_encode($data)]);
    }
}
