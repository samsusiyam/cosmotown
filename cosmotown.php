<?php

/*
 * Decoded and modified - License removed
 * Original: ioncube encoded WHMCS Cosmotown Domain Registrar Module
 */
const COSMOTOWN_API_LIVE_URL = 'https://cosmotown.com/v1/reseller';
const COSMOTOWN_API_SANDBOX_URL = 'https://sandbox.cosmotown.com/v1/reseller';

function cosmotown_getApiBaseUrl($params)
{
    $mode = isset($params['APIMode']) ? $params['APIMode'] : 'Live';
    switch ($mode) {
        case 'Sandbox':
            return COSMOTOWN_API_SANDBOX_URL;
        default:
            return COSMOTOWN_API_LIVE_URL;
    }
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
    return ['FriendlyName' => ['Type' => 'System', 'Value' => 'Cosmotown'], 'APIKey' => ['FriendlyName' => 'API Key', 'Type' => 'password', 'Size' => '255', 'Default' => '', 'Description' => 'Enter API Key Here'], 'APIMode' => ['FriendlyName' => 'API Mode', 'Type' => 'dropdown', 'Options' => ['Live Mode' => 'Production (cosmotown.com)', 'Sandbox' => 'Sandbox (sandbox.cosmotown.com)'], 'Default' => 'Live Mode', 'Description' => 'Select API Mode'], 'CouponCode' => ['FriendlyName' => 'Coupon Code', 'Type' => 'text', 'Size' => '255', 'Default' => '', 'Description' => 'Enter Coupon Code Here (If Available in Cosmotown)']];
}
function cosmotown_RegisterDomain($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->register($params, $params['CouponCode']);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}
function cosmotown_TransferDomain($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->transfer($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}
function cosmotown_RenewDomain($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->renew($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}
function cosmotown_GetNameservers($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
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
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->saveNameserver($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}
function cosmotown_mapContact($c)
{
    return [
        'First Name' => $c['first_name'] ?? $c['FirstName'] ?? '',
        'Last Name' => $c['last_name'] ?? $c['LastName'] ?? '',
        'Company Name' => $c['company'] ?? $c['Company'] ?? '',
        'Email Address' => $c['email'] ?? $c['Email'] ?? '',
        'Address 1' => $c['address1'] ?? $c['Address1'] ?? '',
        'Address 2' => $c['address2'] ?? $c['Address2'] ?? '',
        'City' => $c['city'] ?? $c['City'] ?? '',
        'State' => $c['state'] ?? $c['State'] ?? '',
        'Postcode' => $c['zip'] ?? $c['Zip'] ?? '',
        'Country' => $c['country'] ?? $c['Country'] ?? '',
        'Phone Number' => $c['phone'] ?? $c['Phone'] ?? '',
        'Fax Number' => $c['fax'] ?? $c['Fax'] ?? '',
    ];
}

function cosmotown_extractContacts($response)
{
    if (isset($response['registrant']['contacts'])) {
        $contacts = $response['registrant']['contacts'];
        return [
            'Registrant' => isset($contacts['registrant']) ? cosmotown_mapContact($contacts['registrant']) : [],
            'Technical' => isset($contacts['technical']) ? cosmotown_mapContact($contacts['technical']) : [],
            'Billing' => isset($contacts['billing']) ? cosmotown_mapContact($contacts['billing']) : [],
            'Admin' => isset($contacts['administrative']) ? cosmotown_mapContact($contacts['administrative']) : [],
        ];
    }
    return [
        'Registrant' => isset($response['registrant']) ? cosmotown_mapContact($response['registrant']) : [],
        'Technical' => isset($response['technical']) ? cosmotown_mapContact($response['technical']) : [],
        'Billing' => isset($response['billing']) ? cosmotown_mapContact($response['billing']) : [],
        'Admin' => isset($response['administrative']) ? cosmotown_mapContact($response['administrative']) : [],
    ];
}

function cosmotown_GetContactDetails($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->getContact($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return cosmotown_extractContacts($response);
}
function cosmotown_SaveContactDetails($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->saveContact($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}
function cosmotown_CheckAvailability($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->search($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    $results = (new \WHMCS\Domains\DomainLookup\ResultsList());
    foreach ($response['domains'] as $domain) {
        $searchResult = (new \WHMCS\Domains\DomainLookup\SearchResult($domain['sld'], $domain['tld']));
        if ($domain['status']) {
            if ($domain['status'] == 'available') {
                $status = \WHMCS\Domains\DomainLookup\SearchResult::STATUS_NOT_REGISTERED;
            } elseif ($domain['status'] == 'not_available') {
                $status = \WHMCS\Domains\DomainLookup\SearchResult::STATUS_REGISTERED;
            } else {
                $status = \WHMCS\Domains\DomainLookup\SearchResult::STATUS_TLD_NOT_SUPPORTED;
            }
            $searchResult->setStatus($status);
            $results->append($searchResult);
        }
    }
    return $results;
}
function cosmotown_GetRegistrarLock($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->getInfo($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    if (isset($response['locked']) && $response['locked']) {
        return 'locked';
    }
    return 'unlocked';
}
function cosmotown_SaveRegistrarLock($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->lockDomain($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return ['success' => true];
}
function cosmotown_GetDNS($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->getDNS($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    $hostRecords = [];
    foreach ($response['records'] as $record) {
        $hostRecords[] = ['hostname' => $record['host'], 'type' => $record['type'], 'address' => $record['pointto'], 'priority' => $record['priority'], 'ttl' => $record['ttl']];
    }
    return $hostRecords;
}
function cosmotown_SaveDNS($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $records = cosmotown_formatRecords($params['dnsrecords']);
    $cosmotown->saveDNS($params, $records);
    return ['success' => 'success'];
}
function cosmotown_IDProtectToggle($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->idProtect($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return ['success' => 'success'];
}
function cosmotown_GetEPPCode($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->eppCode($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    if ((isset($response['auth_code']) && !empty($response['auth_code']))) {
        return ['eppcode' => $response['auth_code']];
    }
    return ['success' => 'success'];
}
function cosmotown_Sync($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->getInfo($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    $expiryDate = isset($response['expirydate']) ? $response['expirydate'] : (isset($response['expiration_date']) ? $response['expiration_date'] : date('Y-m-d'));
    return ['expirydate' => $expiryDate, 'active' => $response['status'], 'expired' => ($expiryDate < date('Y-m-d')), 'transferredAway' => false];
}
function cosmotown_formatRecords($records)
{
    $formattedRecords = [];
    foreach ($records as $record) {
        $type = $record['type'];
        $hostname = $record['hostname'];
        $address = $record['address'];
        $priority = isset($record['priority']) ? $record['priority'] : '';
        if (!$type) {
            continue;
        }
        $dnsInfo = ['ttl' => 300, 'priority' => ($priority !== '' ? (int)$priority : 0), 'pointto' => $address, 'host' => $hostname];
        $formattedRecords[] = $dnsInfo;
    }
    return $formattedRecords;
}
function cosmotown_TestConnection($params)
{
    try {
        $cosmotown = cosmotown_getApiInstance($params);
        $response = $cosmotown->ping();
        if (isset($response['ip'])) {
            return ['success' => true, 'msg' => 'Connection successful. IP: ' . $response['ip']];
        }
        return ['success' => false, 'error' => 'Unexpected response: ' . json_encode($response)];
    } catch (\Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
