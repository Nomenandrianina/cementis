<?php
 
return [
    /*
    |--------------------------------------------------------------------------
    | GPS Tracking API Configuration
    |--------------------------------------------------------------------------
    */
 
    'base_url' => env('GPS_API_URL', 'https://www.m-tectracking.mg/api/api.php'),
    'api_key'  => env('GPS_API_KEY', '60EBAD7DC468E6AB6D0DAB89CAAA1073'),
 
    /*
    | Durée de mise en cache des réponses API (en secondes)
    | 0 = pas de cache
    */
    'cache_ttl' => env('GPS_CACHE_TTL', 300),
];
 