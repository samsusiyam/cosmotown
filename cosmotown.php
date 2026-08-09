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
    $response = $cosmotown->transfer();
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
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return null;
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
function cosmotown_GetContactDetails($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->getContact($params);
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return ['Registrant' => ['First Name' => $response['registrant']['FirstName'], 'Last Name' => $response['registrant']['LastName'], 'Company Name' => $response['registrant']['Company'], 'Email Address' => $response['registrant']['Email'], 'Address 1' => $response['registrant']['Address1'], 'Address 2' => $response['registrant']['Address2'], 'City' => $response['registrant']['City'], 'State' => $response['registrant']['State'], 'Postcode' => $response['registrant']['Zip'], 'Country' => $response['registrant']['Country'], 'Phone Number' => $response['registrant']['Phone'], 'Fax Number' => $response['registrant']['Fax']], 'Technical' => ['First Name' => $response['technical']['FirstName'], 'Last Name' => $response['technical']['LastName'], 'Company Name' => $response['technical']['Company'], 'Email Address' => $response['technical']['Email'], 'Address 1' => $response['technical']['Address1'], 'Address 2' => $response['technical']['Address2'], 'City' => $response['technical']['City'], 'State' => $response['technical']['State'], 'Postcode' => $response['technical']['Zip'], 'Country' => $response['technical']['Country'], 'Phone Number' => $response['technical']['Phone'], 'Fax Number' => $response['technical']['Fax']], 'Billing' => ['First Name' => $response['billing']['FirstName'], 'Last Name' => $response['billing']['LastName'], 'Company Name' => $response['billing']['Company'], 'Email Address' => $response['billing']['Email'], 'Address 1' => $response['billing']['Address1'], 'Address 2' => $response['billing']['Address2'], 'City' => $response['billing']['City'], 'State' => $response['billing']['State'], 'Postcode' => $response['billing']['Zip'], 'Country' => $response['billing']['Country'], 'Phone Number' => $response['billing']['Phone'], 'Fax Number' => $response['billing']['Fax']], 'Admin' => ['First Name' => $response['administrative']['FirstName'], 'Last Name' => $response['administrative']['LastName'], 'Company Name' => $response['administrative']['Company'], 'Email Address' => $response['administrative']['Email'], 'Address 1' => $response['administrative']['Address1'], 'Address 2' => $response['administrative']['Address2'], 'City' => $response['administrative']['City'], 'State' => $response['administrative']['State'], 'Postcode' => $response['administrative']['Zip'], 'Country' => $response['administrative']['Country'], 'Phone Number' => $response['administrative']['Phone'], 'Fax Number' => $response['administrative']['Fax']]];
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
    if ($response['locked']) {
        return 'locked';
    }
    return 'unlocked';
}
function cosmotown_SaveRegistrarLock($params)
{
    $cosmotown = (cosmotown_getApiInstance($params));
    $response = $cosmotown->lockDomain();
    if ((isset($response['status']) && ($response['status'] === 'error'))) {
        return ['error' => $response['message']];
    }
    return ['success' => 'success'];
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
    $expiryDate = isset($response['expirydate']) ? $response['expirydate'] : date('Y-m-d');
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
