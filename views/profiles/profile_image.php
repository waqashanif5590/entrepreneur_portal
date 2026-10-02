<?php
require_once __DIR__ . '/../../config/database.php';

$agentId = filter_input(INPUT_GET, 'agent_account_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($agentId === false || $agentId === null) {
    http_response_code(400);
    exit('Invalid profile.');
}

$statement = $conn->prepare('SELECT profile_image FROM profiles WHERE agent_account_id = ? LIMIT 1');
$statement->bind_param('i', $agentId);
$statement->execute();
$profile = $statement->get_result()->fetch_assoc();
$statement->close();
$fileName = basename($profile['profile_image'] ?? '');
$filePath = dirname(__DIR__, 2) . '/uploads/profiles/' . $fileName;
if ($fileName === '' || !is_file($filePath)) {
    http_response_code(404);
    exit('Profile image not found.');
}

$mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($filePath);
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mimeType, $allowedTypes, true)) {
    http_response_code(404);
    exit('Profile image not found.');
}

header('Content-Type: ' . $mimeType);
header('X-Content-Type-Options: nosniff');
readfile($filePath);
