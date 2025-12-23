<?php

//include '../../custom/Helpers/CustomHelper.php';

define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);

function get_client_ip()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }

    $ipAddressArr = ['127.0.0.1', '172.20.0.1', '172.20.0.4', '10.0.0.4', '::1'];

    if (in_array($ipaddress, $ipAddressArr)) {
        $ipaddress = '212.34.11.204';
    }


    $ipArr = explode(',', $ipaddress);

    if (count($ipArr) > 1) {
        return $ipArr[1];
    }
    return $ipArr[0];
}


/**
 * This function extract the location of the user using the IP address.
 */
function getLocation()
{
    $ip = get_client_ip();
    $country = null;
    $city = null;
    $countryCode = null;
    $ip_lat = null;
    $ip_lng = null;
    $continentCode = null;
    $continent = null;

    // Check if MaxMind library and database are available
    $db = resource_path() . '/geolite2/GeoLite2-City.mmdb';
    if (class_exists('MaxMind\Db\Reader') && file_exists($db)) {
        try {
            $reader = new \MaxMind\Db\Reader($db);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $res = $reader->get($ip);

                if ($res) {
                    $country = $res['country']['names']['en'] ?? null;
                    $city = $res['city']['names']['en'] ?? null;
                    $countryCode = $res['country']['iso_code'] ?? null;
                    $ip_lat = $res['location']['latitude'] ?? null;
                    $ip_lng = $res['location']['longitude'] ?? null;
                    $continent = $res['continent']['names']['en'] ?? '';
                    $continentCode = $res['continent']['code'] ?? '';
                }
            }
        } catch (\Exception $e) {
            // Silently fail if GeoIP lookup fails
        }
    }

    return [
        'ip' => $ip ?? '',
        "country" => $country ?? '',
        "city" => $city ?? '',
        "countryCode" => $countryCode ?? '',
        "ip_lat" => $ip_lat ?? 0,
        "ip_lng" => $ip_lng ?? 0,
        "continent" => $continent ?? '',
        "continentCode" => $continentCode ?? '',
    ];
}



