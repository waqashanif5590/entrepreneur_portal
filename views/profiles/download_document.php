<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

$role = requireAccountRole($conn, ['admin', 'agent']);
$agentId = requestPositiveId($_GET['agent_account_id'] ?? null);
$document = $_GET['document'] ?? '';
$documentColumns = [
    'cnic' => 'cnic',
    'resume' => 'resume',
    'certificate' => 'certificate',
];
if ($agentId === null || !is_string($document) || !isset($documentColumns[$document])) {
    http_response_code(400);
    exit('Invalid document request.');
}
if ($role === 'agent' && (int)$_SESSION['id'] !== $agentId) {
    http_response_code(403);
    exit('Access denied.');
}

$column = $documentColumns[$document];
$statement = $conn->prepare("SELECT `$column` AS file_name FROM profiles WHERE agent_account_id = ? LIMIT 1");
$statement->bind_param('i', $agentId);
$statement->execute();
$profile = $statement->get_result()->fetch_assoc();
$statement->close();
$fileName = basename($profile['file_name'] ?? '');
$privateFilePath = dirname(__DIR__, 2) . '/storage/private_profiles/' . $fileName;
$legacyFilePath = dirname(__DIR__, 2) . '/uploads/profiles/' . $fileName;
$filePath = is_file($privateFilePath) ? $privateFilePath : $legacyFilePath;
if ($fileName === '' || !is_file($filePath)) {
    http_response_code(404);
    exit('Document not found.');
}

$mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($filePath);
$allowedTypes = $document === 'cnic'
    ? ['image/jpeg', 'image/png', 'image/gif']
    : ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/zip'];
if (!in_array($mimeType, $allowedTypes, true)) {
    http_response_code(404);
    exit('Document not found.');
}

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('X-Content-Type-Options: nosniff');
readfile($filePath);
