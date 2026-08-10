<?php

namespace WHMCS\Module\Registrar\Cosmotown;

class CosmotownApi
{
    public $apiKey;
    public $cosmotown;

    public function __construct($apiKey, $baseUrl = 'https://cosmotown.com/v1/reseller')
    {
        $this->apiKey = $apiKey;
        require_once __DIR__ . '/vendor/autoload.php';
        $this->cosmotown = new \RtRaselBD\Cosmotown\Cosmotown($apiKey, $baseUrl);
    }

    public function ping()
    {
        return $this->cosmotown->ping();
    }

    public function register($params, $coupon = '')
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        $result = $this->cosmotown->registerDomain($domain, $params['regperiod'], $coupon);
        if (is_array($result) && isset($result[0]) && isset($result[0]['status']) && $result[0]['status'] === 'error') {
            return ['status' => 'error', 'message' => $result[0]['message']];
        }
        return ['status' => 'success'];
    }

    public function transfer($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        $result = $this->cosmotown->transferDomain($domain, $params['eppcode']);
        if (is_array($result) && isset($result[0]) && isset($result[0]['status']) && $result[0]['status'] === 'error') {
            return ['status' => 'error', 'message' => $result[0]['message']];
        }
        return ['status' => 'success'];
    }

    public function renew($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        $result = $this->cosmotown->renewDomain($domain, $params['regperiod']);
        return ['status' => 'success'];
    }

    public function getNameserver($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        $data = $this->cosmotown->getDomainInfo($domain);
        if (isset($data['nameservers'])) {
            return $data['nameservers'];
        }
        return [];
    }

    public function saveNameserver($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        $nameservers = array_filter([$params['ns1'], $params['ns2'], $params['ns3'], $params['ns4']]);
        $result = $this->cosmotown->saveDomainNameservers($domain, $nameservers);
        return ['status' => 'success'];
    }

    public function getContact($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->getContactInfo($domain);
    }

    public function saveContact($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        $contactData = $this->mapContactDetails($params['contactdetails']);
        $result = $this->cosmotown->saveContactInfo($domain, $contactData);
        if (isset($result['error_message'])) {
            return ['status' => 'error', 'message' => $result['error_message']];
        }
        return ['status' => 'success'];
    }

    public function search($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->searchDomain($domain);
    }

    public function getInfo($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->getDomainInfo($domain);
    }

    public function lockDomain($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        $locked = isset($params['lockenabled']) && $params['lockenabled'] === 'locked';
        try {
            $this->cosmotown->saveDomainInfo($domain, ['lock_domain' => $locked]);
            return ['status' => 'success'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function idProtect($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        $enabled = isset($params['protectenable']) && $params['protectenable'];
        $this->cosmotown->saveDomainInfo($domain, ['enable_private_whois' => $enabled]);
        return ['status' => 'success'];
    }

    public function eppCode($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->getDomainEPPCode($domain);
    }

    public function getDNS($params)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->getDomainDNS($domain);
    }

    public function saveDNS($params, $records)
    {
        $domain = $params['sld'] . '.' . $params['tld'];
        $this->cosmotown->saveDomainDNS($domain, $records);
        return ['status' => 'success'];
    }

    private function mapContactDetails($details)
    {
        $map = function ($c) {
            return [
                'FirstName' => $c['First Name'] ?? '',
                'LastName' => $c['Last Name'] ?? '',
                'Company' => $c['Company Name'] ?? '',
                'Phone' => $c['Phone Number'] ?? '',
                'Extension' => null,
                'Fax' => $c['Fax Number'] ?? '',
                'City' => $c['City'] ?? '',
                'State' => $c['State'] ?? '',
                'Zip' => $c['Postcode'] ?? '',
                'Country' => $c['Country'] ?? '',
                'Email' => $c['Email Address'] ?? '',
                'Address1' => $c['Address 1'] ?? '',
                'Address2' => $c['Address 2'] ?? '',
            ];
        };

        return [
            'registrant' => $map($details['Registrant']),
            'administrative' => $map($details['Admin']),
            'technical' => $map($details['Technical']),
            'billing' => $map($details['Billing']),
        ];
    }
}
