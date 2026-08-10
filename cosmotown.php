<?php

const COSMOTOWN_API_LIVE_URL = 'https://cosmotown.com/v1/reseller';
const COSMOTOWN_API_SANDBOX_URL = 'https://sandbox.cosmotown.com/v1/reseller';

function cosmotown_getApiBaseUrl($params)
{
    $mode = isset($params['APIMode']) ? $params['APIMode'] : 'Live';
    if ($mode === 'Sandbox') {
        return COSMOTOWN_API_SANDBOX_URL;
    }
    return COSMOTOWN_API_LIVE_URL;
}

function cosmotown_getApiInstance($params)
{
    $baseUrl = cosmotown_getApiBaseUrl($params);
    return new \WHMCS\Module\Registrar\Cosmotown\CosmotownApi($params['APIKey'], $baseUrl);
}

function cosmotown_MetaData()
{
    return ['DisplayName' => 'Cosmotown', 'APIVersion' => '1.1'];
}

function cosmotown_getConfigArray()
{
    return [
        'FriendlyName' => ['Type' => 'System', 'Value' => 'Cosmotown'],
        'APIKey' => ['FriendlyName' => 'API Key', 'Type' => 'password', 'Size' => '255', 'Default' => '', 'Description' => 'Enter API Key Here'],
        'APIMode' => ['FriendlyName' => 'API Mode', 'Type' => 'dropdown', 'Options' => ['Live Mode' => 'Production (cosmotown.com)', 'Sandbox' => 'Sandbox (sandbox.cosmotown.com)'], 'Default' => 'Live Mode', 'Description' => 'Select API Mode'],
        'CouponCode' => ['FriendlyName' => 'Coupon Code', 'Type' => 'text', 'Size' => '255', 'Default' => '', 'Description' => 'Enter Coupon Code Here (If Available in Cosmotown)'],
    ];
}

function cosmotown_TestConnection($params)
{
    try {
        $cosmotown = cosmotown_getApiInstance($params);
        $response = $cosmotown->ping();
        if (isset($response['ip'])) {
            return ['success' => true, 'msg' => 'Connection successful. IP: ' . $response['ip']];
        }
        return ['success' => false, 'error' => 'Unexpected response'];
    } catch (\Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function cosmotown_RegisterDomain($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->register($params, $params['CouponCode']);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}

function cosmotown_TransferDomain($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->transfer($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}

function cosmotown_RenewDomain($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->renew($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}

function cosmotown_GetNameservers($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->getNameserver($params);
    if (!is_array($response)) {
        return null;
    }
    $ns = [];
    for ($i = 1; $i <= 5; $i++) {
        $ns['ns' . $i] = isset($response[$i - 1]) ? $response[$i - 1] : '';
    }
    return $ns;
}

function cosmotown_SaveNameservers($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->saveNameserver($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}

function cosmotown_mapContact($c)
{
    return [
        'First Name' => $c['FirstName'] ?? '',
        'Last Name' => $c['LastName'] ?? '',
        'Company Name' => $c['Company'] ?? '',
        'Email Address' => $c['Email'] ?? '',
        'Address 1' => $c['Address1'] ?? '',
        'Address 2' => $c['Address2'] ?? '',
        'City' => $c['City'] ?? '',
        'State' => $c['State'] ?? '',
        'Postcode' => $c['Zip'] ?? '',
        'Country' => $c['Country'] ?? '',
        'Phone Number' => $c['Phone'] ?? '',
        'Fax Number' => $c['Fax'] ?? '',
    ];
}

function cosmotown_GetContactDetails($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->getContact($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    return [
        'Registrant' => isset($response['registrant']) ? cosmotown_mapContact($response['registrant']) : [],
        'Technical' => isset($response['technical']) ? cosmotown_mapContact($response['technical']) : [],
        'Billing' => isset($response['billing']) ? cosmotown_mapContact($response['billing']) : [],
        'Admin' => isset($response['administrative']) ? cosmotown_mapContact($response['administrative']) : [],
    ];
}

function cosmotown_SaveContactDetails($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->saveContact($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}

function cosmotown_CheckAvailability($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->search($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    $results = new \WHMCS\Domains\DomainLookup\ResultsList();
    foreach ($response['domains'] as $domain) {
        $searchResult = new \WHMCS\Domains\DomainLookup\SearchResult($domain['sld'], $domain['tld']);
        if (isset($domain['status'])) {
            if ($domain['status'] === 'available') {
                $status = \WHMCS\Domains\DomainLookup\SearchResult::STATUS_NOT_REGISTERED;
            } elseif ($domain['status'] === 'not_available') {
                $status = \WHMCS\Domains\DomainLookup\SearchResult::STATUS_REGISTERED;
            } else {
                $status = \WHMCS\Domains\DomainLookup\SearchResult::STATUS_TLD_NOT_SUPPORTED;
            }
            $searchResult->setStatus($status);
        }
        $results->append($searchResult);
    }
    return $results;
}

function cosmotown_GetRegistrarLock($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->getInfo($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    if (isset($response['locked']) && $response['locked']) {
        return 'locked';
    }
    return 'unlocked';
}

function cosmotown_SaveRegistrarLock($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->lockDomain($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}

function cosmotown_GetDNS($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->getDNS($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    $hostRecords = [];
    $records = isset($response['records']) ? $response['records'] : $response;
    if (is_array($records)) {
        foreach ($records as $type => $typeRecords) {
            if (!is_array($typeRecords)) {
                continue;
            }
            foreach ($typeRecords as $record) {
                $hostRecords[] = [
                    'hostname' => $record['host'] ?? '',
                    'type' => $type,
                    'address' => $record['pointto'] ?? $record['content'] ?? '',
                    'priority' => $record['priority'] ?? 0,
                    'ttl' => $record['ttl'] ?? 300,
                ];
            }
        }
    }
    return $hostRecords;
}

function cosmotown_SaveDNS($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $records = cosmotown_formatRecords($params['dnsrecords']);
    $cosmotown->saveDNS($params, $records);
    return ['success' => true];
}

function cosmotown_IDProtectToggle($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->idProtect($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}

function cosmotown_GetEPPCode($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->eppCode($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    if (isset($response['auth_code']) && !empty($response['auth_code'])) {
        return ['eppcode' => $response['auth_code']];
    }
    return ['success' => true];
}

function cosmotown_Sync($params)
{
    $cosmotown = cosmotown_getApiInstance($params);
    $response = $cosmotown->getInfo($params);
    if (isset($response['status']) && $response['status'] === 'error') {
        return ['error' => $response['message']];
    }
    $expiryDate = $response['expiration_date'] ?? date('Y-m-d');
    return [
        'expirydate' => $expiryDate,
        'active' => true,
        'expired' => ($expiryDate < date('Y-m-d')),
        'transferredAway' => false,
    ];
}

function cosmotown_formatRecords($records)
{
    $formatted = [];
    foreach ($records as $record) {
        $type = strtoupper($record['type']);
        if (!$type) {
            continue;
        }
        $entry = [
            'host' => $record['hostname'],
            'ttl' => isset($record['ttl']) ? (int) $record['ttl'] : 300,
            'priority' => isset($record['priority']) ? (int) $record['priority'] : 0,
        ];
        if ($type === 'TXT') {
            $entry['content'] = $record['address'];
        } else {
            $entry['pointto'] = $record['address'];
        }
        $formatted[$type][] = $entry;
    }
    return $formatted;
}
