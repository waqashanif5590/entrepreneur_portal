<?php
// get_countries.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Optional, but safe here

$cacheFile = __DIR__ . '/../../public/assets/json/countries_cache.json';
$cacheTime = 86400 * 7; // Cache for 7 days

// Use cached data if fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

// Fetch from API
$ch = curl_init('https://restcountries.com/v3.1/all?fields=name');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    // Optional: Light processing (sort by common name)
    $data = json_decode($response, true);
    usort($data, function ($a, $b) {
        return strcmp($a['name']['common'] ?? '', $b['name']['common'] ?? '');
    });

    $json = json_encode($data);
    file_put_contents($cacheFile, $json); // Cache it
    echo $json;
} else {
    // Fallback: Return minimal static list or error
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch countries']);
}
