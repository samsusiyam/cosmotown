<?php

namespace RtRaselBD\Cosmotown;

class Cosmotown
{
    public $administrative;
    public $apiKey;
    public $billing;
    public $domainContactInfoCache = [];
    public $domainInfoCache = [];
    public $httpClient;
    public $nameservers;
    public $registrant;
    public $technical;

    public function __construct($apiKey, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new \RtRaselBD\Cosmotown\Http\Client($this->apiKey, $baseUrl);
    }

    public function handleResponse($response)
    {
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);
        if ($statusCode >= 200 && $statusCode < 300) {
            return $body;
        }
        $message = isset($body['message']) ? $body['message'] : 'API request failed with status code: ' . $statusCode;
        throw new \RtRaselBD\Cosmotown\Exceptions\CosmotownException($message);
    }

    public function logDebug($method, $data)
    {
        $logFile = dirname(__DIR__, 2) . '/cosmotown_debug.log';
        $entry = date('[Y-m-d H:i:s]') . " [$method] " . json_encode($data) . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND);
    }

    public function testConnection()
    {
        $response = $this->httpClient->get('test');
        return $this->handleResponse($response);
    }

    public function registerDomain($domain, $years = 1, $couponId = '')
    {
        $requestData = ['coupon_id' => $couponId, 'items' => [['name' => $domain, 'years' => (int)$years]]];
        $response = $this->httpClient->post('registerdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function registerDomains($domains, $couponId = '')
    {
        $requestData = ['coupon_id' => $couponId, 'items' => $domains];
        $response = $this->httpClient->post('registerdomains', $requestData);
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

    public function saveDomainNameserver($domain, $nameservers)
    {
        $requestData = ['domain' => $domain, 'nameservers' => $nameservers];
        $response = $this->httpClient->post('savedomainnameservers', $requestData);
        return $this->handleResponse($response);
    }

    public function getDomainInfo($domain)
    {
        if (!isset($this->domainInfoCache[$domain])) {
            $response = $this->httpClient->get('domaininfo', ['domain' => $domain]);
            $result = $this->handleResponse($response);
            $this->logDebug('getDomainInfo', ['domain' => $domain, 'response' => $result]);
            $this->domainInfoCache[$domain] = $result;
        }
        return $this->domainInfoCache[$domain];
    }

    public function getNameservers($domain)
    {
        $responseData = $this->getDomainInfo($domain);
        if (isset($responseData['nameservers'])) {
            return $responseData['nameservers'];
        }
        if (isset($responseData['name_servers'])) {
            return $responseData['name_servers'];
        }
        if (isset($responseData['ns1'])) {
            $ns = [];
            for ($i = 1; $i <= 4; $i++) {
                if (isset($responseData['ns' . $i]) && !empty($responseData['ns' . $i])) {
                    $ns[] = $responseData['ns' . $i];
                }
            }
            return $ns;
        }
        if (isset($responseData['data']['nameservers'])) {
            return $responseData['data']['nameservers'];
        }
        if (isset($responseData['data']['name_servers'])) {
            return $responseData['data']['name_servers'];
        }
        return [];
    }

    public function getDomainContactInfo($domain)
    {
        if (!isset($this->domainContactInfoCache[$domain])) {
            $response = $this->httpClient->get('contactinfo', ['domain' => $domain]);
            $this->domainContactInfoCache[$domain] = $this->handleResponse($response);
        }
        return $this->domainContactInfoCache[$domain];
    }

    public function saveDomainContactInfo($domain, $registrant, $administrative, $technical, $billing)
    {
        $requestData = ['registrant' => $registrant, 'administrative' => $administrative, 'technical' => $technical, 'billing' => $billing];
        $endpoint = 'contactinfo?domain=' . $domain;
        $response = $this->httpClient->post($endpoint, $requestData);
        return $this->handleResponse($response);
    }

    public function getAllContactInfo($domain)
    {
        return $this->getDomainContactInfo($domain);
    }

    public function getRegistrantContactInfo($domain)
    {
        $contactInfo = $this->getAllContactInfo($domain);
        if (!isset($contactInfo['registrant'])) {
            throw new \RtRaselBD\Cosmotown\Exceptions\CosmotownException('Registrant contact not found in the domain information response');
        }
        return $contactInfo['registrant'];
    }

    public function getAdministrativeContactInfo($domain)
    {
        $contactInfo = $this->getAllContactInfo($domain);
        if (!isset($contactInfo['administrative'])) {
            throw new \RtRaselBD\Cosmotown\Exceptions\CosmotownException('Administrative contact not found in the domain information response');
        }
        return $contactInfo['administrative'];
    }

    public function getTechnicalContactInfo($domain)
    {
        $contactInfo = $this->getAllContactInfo($domain);
        if (!isset($contactInfo['technical'])) {
            throw new \RtRaselBD\Cosmotown\Exceptions\CosmotownException('Technical contact not found in the domain information response');
        }
        return $contactInfo['technical'];
    }

    public function getBillingContactInfo($domain)
    {
        $contactInfo = $this->getAllContactInfo($domain);
        if (!isset($contactInfo['billing'])) {
            throw new \RtRaselBD\Cosmotown\Exceptions\CosmotownException('Billing contact not found in the domain information response');
        }
        return $contactInfo['billing'];
    }

    public function getDomainDnsRecords($domain)
    {
        $response = $this->httpClient->get('getdomaindnssettings', ['domain' => $domain]);
        return $this->handleResponse($response);
    }

    public function saveDnsRecords($domain, $records)
    {
        $requestData = ['domain' => $domain, 'records' => $records];
        $response = $this->httpClient->post('savedomaindnssettings', $requestData);
        return $this->handleResponse($response);
    }

    public function renewDomain($domain, $years = 1)
    {
        $requestData = ['items' => [['name' => $domain, 'years' => (int)$years]]];
        $response = $this->httpClient->post('renewdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function renewDomains($domains)
    {
        $requestData = ['items' => $domains];
        $response = $this->httpClient->post('renewdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function saveDomainInfo($domain, $options = [])
    {
        $requestData = ['domain' => $domain, 'options' => $options];
        $response = $this->httpClient->post('domaininfo', $requestData);
        return $this->handleResponse($response);
    }

    public function transferDomain($domain, $authCode)
    {
        $requestData = ['items' => [['name' => $domain, 'authCode' => base64_encode($authCode)]]];
        $response = $this->httpClient->post('transferdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function transferDomains($domains)
    {
        $requestData = ['items' => $domains];
        $response = $this->httpClient->post('transferdomains', $requestData);
        return $this->handleResponse($response);
    }

    public function getDomainEPPCode($domain)
    {
        $response = $this->httpClient->get('domainepp', ['domain' => $domain]);
        return $this->handleResponse($response);
    }
}
