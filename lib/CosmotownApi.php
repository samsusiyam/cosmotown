<?php

namespace WHMCS\Module\Registrar\Cosmotown;

class CosmotownApi {
    public $apiKey;
    public $cosmotown;
    public $description;
    public $domains;
    public $localkey;
    public $md5hash;
    public $status;

    public function __construct($apiKey, $baseUrl = 'https://cosmotown.com/v1/reseller') {
        $this->apiKey = $apiKey;
        require_once __DIR__ . '/vendor/autoload.php';
        $this->cosmotown = new \RtRaselBD\Cosmotown\Cosmotown($apiKey, $baseUrl);
    }

    public function register($params, $coupon) {
        $domain = $params['sld'] . '.' . $params['tld'];
        $search = $this->search($params);
        if (((isset($search['domains']) && $search['domains'][0]) && ($search['domains'][0]['status'] === 'not_available'))) {
            return ['status' => 'error', 'message' => $search['domains'][0]['message']];
        }
        $this->cosmotown->registerDomain($domain, $params['regperiod'], $coupon);
        $nameserver = $this->saveNameserver($params);
        if ((isset($nameserver['status']) && ($nameserver['status'] === 'error'))) {
            return ['status' => 'error', 'message' => $nameserver['message']];
        }
        return ['status' => 'success'];
    }

    public function transfer($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        $this->cosmotown->transferDomain($domain, $params['eppcode']);
        return ['status' => 'success'];
    }

    public function renew($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        $this->cosmotown->renewDomain($domain, $params['regperiod'], $params['regperiod']);
        return ['status' => 'success'];
    }

    public function getNameserver($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->getNameservers($domain);
    }

    public function saveNameserver($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        $nameservers = [$params['ns1'], $params['ns2'], $params['ns3'], $params['ns4']];
        $response = $this->cosmotown->saveDomainNameserver($domain, $nameservers);
        if (isset($response['status']) && $response['status'] === 'error') {
            return ['status' => 'error', 'message' => $response['message']];
        }
        return ['status' => 'success'];
    }

    public function getContact($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->getAllContactInfo($domain);
    }

    public function saveContact($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        $contactDetails = $params['contactdetails'];
        $registrant = [
            'FirstName' => $contactDetails['Registrant']['First Name'],
            'LastName' => $contactDetails['Registrant']['Last Name'],
            'Company' => $contactDetails['Registrant']['Company Name'],
            'Phone' => $contactDetails['Registrant']['Phone Number'],
            'Extension' => null,
            'Fax' => $contactDetails['Registrant']['Fax Number'],
            'City' => $contactDetails['Registrant']['City'],
            'State' => $contactDetails['Registrant']['State'],
            'Zip' => $contactDetails['Registrant']['Postcode'],
            'Country' => $contactDetails['Registrant']['Country'],
            'Email' => $contactDetails['Registrant']['Email Address'],
            'Address1' => $contactDetails['Registrant']['Address 1'],
            'Address2' => $contactDetails['Registrant']['Address 2']
        ];
        $administrative = [
            'FirstName' => $contactDetails['Admin']['First Name'],
            'LastName' => $contactDetails['Admin']['Last Name'],
            'Company' => $contactDetails['Admin']['Company Name'],
            'Phone' => $contactDetails['Admin']['Phone Number'],
            'Extension' => null,
            'Fax' => $contactDetails['Admin']['Fax Number'],
            'City' => $contactDetails['Admin']['City'],
            'State' => $contactDetails['Admin']['State'],
            'Zip' => $contactDetails['Admin']['Postcode'],
            'Country' => $contactDetails['Admin']['Country'],
            'Email' => $contactDetails['Admin']['Email Address'],
            'Address1' => $contactDetails['Admin']['Address 1'],
            'Address2' => $contactDetails['Admin']['Address 2']
        ];
        $technical = [
            'FirstName' => $contactDetails['Technical']['First Name'],
            'LastName' => $contactDetails['Technical']['Last Name'],
            'Company' => $contactDetails['Technical']['Company Name'],
            'Phone' => $contactDetails['Technical']['Phone Number'],
            'Extension' => null,
            'Fax' => $contactDetails['Technical']['Fax Number'],
            'City' => $contactDetails['Technical']['City'],
            'State' => $contactDetails['Technical']['State'],
            'Zip' => $contactDetails['Technical']['Postcode'],
            'Country' => $contactDetails['Technical']['Country'],
            'Email' => $contactDetails['Technical']['Email Address'],
            'Address1' => $contactDetails['Technical']['Address 1'],
            'Address2' => $contactDetails['Technical']['Address 2']
        ];
        $billing = [
            'FirstName' => $contactDetails['Billing']['First Name'],
            'LastName' => $contactDetails['Billing']['Last Name'],
            'Company' => $contactDetails['Billing']['Company Name'],
            'Phone' => $contactDetails['Billing']['Phone Number'],
            'Extension' => null,
            'Fax' => $contactDetails['Billing']['Fax Number'],
            'City' => $contactDetails['Billing']['City'],
            'State' => $contactDetails['Billing']['State'],
            'Zip' => $contactDetails['Billing']['Postcode'],
            'Country' => $contactDetails['Billing']['Country'],
            'Email' => $contactDetails['Billing']['Email Address'],
            'Address1' => $contactDetails['Billing']['Address 1'],
            'Address2' => $contactDetails['Billing']['Address 2']
        ];
        return $this->cosmotown->saveDomainContactInfo($domain, $registrant, $administrative, $technical, $billing);
    }

    public function search($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->searchDomain($domain);
    }

    public function getInfo($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->getDomainInfo($domain, true);
    }

    public function lockDomain($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        $info = $this->getInfo($params);
        $lockStatus = $params['lockenabled'];
        $lock = ($lockStatus === 'locked');
        $options = [
            'enable_private_whois' => isset($info['whois_privacy']) ? $info['whois_privacy'] : false,
            'lock_domain' => $lock,
            'enable_auto_billing' => isset($info['auto_billing']) ? $info['auto_billing'] : false
        ];
        $this->cosmotown->logDebug('lockDomain', ['domain' => $domain, 'lockenabled' => $lockStatus, 'lock' => $lock, 'options' => $options]);
        $response = $this->cosmotown->saveDomainInfo($domain, $options);
        $this->cosmotown->logDebug('lockDomain_response', ['response' => $response]);
        return ['status' => 'success'];
    }

    public function idProtect($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        $info = $this->getInfo($params);
        $options = [
            'enable_private_whois' => $params['protectenable'],
            'lock_domain' => isset($info['locked']) ? $info['locked'] : false,
            'enable_auto_billing' => isset($info['auto_billing']) ? $info['auto_billing'] : false
        ];
        $this->cosmotown->saveDomainInfo($domain, $options);
        return ['status' => 'success'];
    }

    public function eppCode($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->getDomainEPPCode($domain);
    }

    public function getDNS($params) {
        $domain = $params['sld'] . '.' . $params['tld'];
        return $this->cosmotown->getDomainDnsRecords($domain);
    }

    public function saveDNS($params, $records) {
        $domain = $params['sld'] . '.' . $params['tld'];
        $nameservers = $this->getNameserver($params);
        if (!in_array('ns1.cosmotown.com', $nameservers)) {
            $nameservers = ['ns1.cosmotown.com', 'ns2.cosmotown.com', 'ns3.cosmotown.com', 'ns4.cosmotown.com'];
            $this->cosmotown->saveDomainNameserver($domain, $nameservers);
        }
        return $this->cosmotown->saveDnsRecords($domain, $records);
    }

    public function verifyLicense($licenseCode, $force_verify = false) {
        return true;
    }

    public function ping() {
        return $this->cosmotown->ping();
    }

    public function getLocalKey() {
        return '';
    }

    public function deleteLocalKey() {
        return true;
    }

    public function setLocalKey($key) {
        return true;
    }
}
