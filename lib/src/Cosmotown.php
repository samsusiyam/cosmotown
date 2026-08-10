<?php

namespace RtRaselBD\Cosmotown;

class Cosmotown
{
    public $apiKey;
    public $httpClient;

    public function __construct($apiKey, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new \RtRaselBD\Cosmotown\Http\Client($this->apiKey, $baseUrl);
    }

    public function handleResponse($response)
    {
        $statusCode = $response->getStatusCode();
        $rawBody = $response->getBody()->getContents();
        $body = json_decode($rawBody, true);

        if ($statusCode >= 200 && $statusCode < 300) {
            return $body;
        }

        $message = isset($body['error_message']) ? $body['error_message'] : 'API request failed with status code: ' . $statusCode;
        throw new \RtRaselBD\Cosmotown\Exceptions\CosmotownException($message);
    }

    public function ping()
    {
        $response = $this->httpClient->get('ping');
        return $this->handleResponse($response);
    }

    public function listDomains($limit = 30, $offset = 0)
    {
        $response = $this->httpClient->get('listdomains', ['limit' => $limit, 'offset' => $offset]);
        return $this->handleResponse($response);
    }

    public function searchDomain($domain)
    {
        $requestData = ['domains' => [$domain]];
        $response = $this->httpClient->post('searchdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function searchDomains($domains)
    {
        $requestData = ['domains' => $domains];
        $response = $this->httpClient->post('searchdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function getDomainInfo($domain)
    {
        $response = $this->httpClient->get('domaininfo', ['domain' => $domain]);
        return $this->handleResponse($response);
    }

    public function registerDomain($domain, $years = 1, $couponId = '')
    {
        $requestData = [
            'coupon_id' => $couponId,
            'items' => [['name' => $domain, 'years' => (int) $years]],
        ];
        $response = $this->httpClient->post('registerdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function registerDomains($domains, $couponId = '')
    {
        $requestData = [
            'coupon_id' => $couponId,
            'items' => $domains,
        ];
        $response = $this->httpClient->post('registerdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function renewDomain($domain, $years = 1)
    {
        $requestData = [
            'items' => [['name' => $domain, 'years' => (int) $years]],
        ];
        $response = $this->httpClient->post('renewdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function renewDomains($domains)
    {
        $requestData = [
            'items' => $domains,
        ];
        $response = $this->httpClient->post('renewdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function transferDomain($domain, $authCode)
    {
        $requestData = [
            'items' => [['name' => $domain, 'authCode' => base64_encode($authCode)]],
        ];
        $response = $this->httpClient->post('transferdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function transferDomains($domains)
    {
        $requestData = [
            'items' => $domains,
        ];
        $response = $this->httpClient->post('transferdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function getDomainStatus($domains)
    {
        $requestData = ['domains' => $domains];
        $response = $this->httpClient->post('domainstatus', $requestData);
        return $this->handleResponse($response);
    }

    public function saveDomainInfo($domain, $options = [])
    {
        $requestData = [
            'domain' => $domain,
            'options' => $options,
        ];
        $response = $this->httpClient->post('domaininfo', $requestData);
        return $this->handleResponse($response);
    }

    public function getDomainEPPCode($domain)
    {
        $response = $this->httpClient->get('domainepp', ['domain' => $domain]);
        return $this->handleResponse($response);
    }

    public function saveDomainNameservers($domain, $nameservers)
    {
        $requestData = [
            'domain' => $domain,
            'nameservers' => $nameservers,
        ];
        $response = $this->httpClient->post('savedomainnameservers', $requestData);
        return $this->handleResponse($response);
    }

    public function getDomainDNS($domain)
    {
        $response = $this->httpClient->get('getdomaindnssettings', ['domain' => $domain]);
        return $this->handleResponse($response);
    }

    public function saveDomainDNS($domain, $records)
    {
        $requestData = [
            'domain' => $domain,
            'records' => $records,
        ];
        $response = $this->httpClient->post('savedomaindnssettings', $requestData);
        return $this->handleResponse($response);
    }

    public function getContactInfo($domain)
    {
        $response = $this->httpClient->get('contactinfo', ['domain' => $domain]);
        return $this->handleResponse($response);
    }

    public function getDefaultContactInfo()
    {
        $response = $this->httpClient->get('contactinfo');
        return $this->handleResponse($response);
    }

    public function saveContactInfo($domain, $contactData)
    {
        $response = $this->httpClient->post('contactinfo?domain=' . $domain, $contactData);
        return $this->handleResponse($response);
    }

    public function getTldPrice($tld)
    {
        $response = $this->httpClient->get('tldprice', ['tld' => $tld]);
        return $this->handleResponse($response);
    }

    public function getDomainDNSSEC($domain)
    {
        $response = $this->httpClient->get('getdomaindnssec', ['domain' => $domain]);
        return $this->handleResponse($response);
    }

    public function enableDomainDNSSEC($domain, $records)
    {
        $requestData = [
            'domain' => $domain,
            'records' => $records,
        ];
        $response = $this->httpClient->post('enabledomaindnssec', $requestData);
        return $this->handleResponse($response);
    }

    public function disableDomainDNSSEC($domain)
    {
        $requestData = ['domain' => $domain];
        $response = $this->httpClient->post('disabledomaindnssec', $requestData);
        return $this->handleResponse($response);
    }
}
