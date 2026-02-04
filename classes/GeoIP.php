<?php
/**
 * GeoIP Class
 * Handles IP geolocation lookup using free API services
 */
class GeoIP {

    // Cache directory for storing geo data
    private static $cacheDir = __DIR__ . '/../cache/geoip/';
    private static $cacheExpiry = 86400; // 24 hours in seconds

    /**
     * Get geolocation data for an IP address
     * Uses ip-api.com (free, no API key required, 45 requests/minute limit)
     *
     * @param string $ip The IP address to lookup
     * @return array|null Geo data or null on failure
     */
    public static function lookup($ip) {
        // Validate IP address
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }

        // Skip private/local IPs
        if (self::isPrivateIP($ip)) {
            return [
                'country' => 'Local',
                'country_code' => 'XX',
                'region' => '',
                'city' => 'Local Network',
                'latitude' => null,
                'longitude' => null
            ];
        }

        // Check cache first
        $cached = self::getFromCache($ip);
        if ($cached !== null) {
            return $cached;
        }

        // Try ip-api.com (free, no API key)
        $geoData = self::lookupIpApi($ip);

        // Fallback to ipapi.co if ip-api.com fails
        if ($geoData === null) {
            $geoData = self::lookupIpApiCo($ip);
        }

        // Cache the result
        if ($geoData !== null) {
            self::saveToCache($ip, $geoData);
        }

        return $geoData;
    }

    /**
     * Lookup using ip-api.com (free, 45 requests/minute)
     */
    private static function lookupIpApi($ip) {
        $url = "http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city,lat,lon";

        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
                'ignore_errors' => true
            ]
        ]);

        try {
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            if (!$data || $data['status'] !== 'success') {
                return null;
            }

            return [
                'country' => $data['country'] ?? null,
                'country_code' => $data['countryCode'] ?? null,
                'region' => $data['regionName'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => $data['lat'] ?? null,
                'longitude' => $data['lon'] ?? null
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Lookup using ipapi.co (free, 1000 requests/day)
     */
    private static function lookupIpApiCo($ip) {
        $url = "https://ipapi.co/{$ip}/json/";

        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
                'ignore_errors' => true,
                'header' => 'User-Agent: PHP'
            ]
        ]);

        try {
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            if (!$data || isset($data['error'])) {
                return null;
            }

            return [
                'country' => $data['country_name'] ?? null,
                'country_code' => $data['country_code'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check if IP is a private/local address
     */
    private static function isPrivateIP($ip) {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Get cached geo data for IP
     */
    private static function getFromCache($ip) {
        $cacheFile = self::getCacheFile($ip);

        if (!file_exists($cacheFile)) {
            return null;
        }

        // Check if cache is expired
        if (filemtime($cacheFile) < (time() - self::$cacheExpiry)) {
            @unlink($cacheFile);
            return null;
        }

        $data = @file_get_contents($cacheFile);
        if ($data === false) {
            return null;
        }

        return json_decode($data, true);
    }

    /**
     * Save geo data to cache
     */
    private static function saveToCache($ip, $data) {
        // Create cache directory if it doesn't exist
        if (!is_dir(self::$cacheDir)) {
            @mkdir(self::$cacheDir, 0755, true);
        }

        $cacheFile = self::getCacheFile($ip);
        @file_put_contents($cacheFile, json_encode($data));
    }

    /**
     * Get cache file path for IP
     */
    private static function getCacheFile($ip) {
        return self::$cacheDir . md5($ip) . '.json';
    }

    /**
     * Get country name from country code
     */
    public static function getCountryName($code) {
        $countries = [
            'AF' => 'Afghanistan',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AR' => 'Argentina',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'BE' => 'Belgium',
            'BR' => 'Brazil',
            'CA' => 'Canada',
            'CL' => 'Chile',
            'CN' => 'China',
            'CO' => 'Colombia',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'EG' => 'Egypt',
            'FI' => 'Finland',
            'FR' => 'France',
            'DE' => 'Germany',
            'GR' => 'Greece',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'MY' => 'Malaysia',
            'MX' => 'Mexico',
            'NL' => 'Netherlands',
            'NZ' => 'New Zealand',
            'NO' => 'Norway',
            'PK' => 'Pakistan',
            'PH' => 'Philippines',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'SA' => 'Saudi Arabia',
            'SG' => 'Singapore',
            'ZA' => 'South Africa',
            'ES' => 'Spain',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'TW' => 'Taiwan',
            'TH' => 'Thailand',
            'TR' => 'Turkey',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'VN' => 'Vietnam',
            'XX' => 'Local'
        ];

        return $countries[$code] ?? $code;
    }

    /**
     * Get flag emoji for country code
     */
    public static function getFlag($countryCode) {
        if (empty($countryCode) || strlen($countryCode) !== 2 || $countryCode === 'XX') {
            return '🌐';
        }

        // Convert country code to flag emoji
        $code = strtoupper($countryCode);
        $flag = '';
        $flag .= mb_chr(0x1F1E6 + ord($code[0]) - ord('A'));
        $flag .= mb_chr(0x1F1E6 + ord($code[1]) - ord('A'));

        return $flag;
    }

    /**
     * Clear the geo cache
     */
    public static function clearCache() {
        if (!is_dir(self::$cacheDir)) {
            return true;
        }

        $files = glob(self::$cacheDir . '*.json');
        foreach ($files as $file) {
            @unlink($file);
        }

        return true;
    }
}
