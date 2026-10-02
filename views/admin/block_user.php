<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

requireAccountRole($conn, ['admin']);
requirePostCsrfToken();
$userId = requestPositiveId($_POST['id'] ?? null);
if ($userId === null) {
    http_response_code(400);
    exit('Invalid user ID.');
}

$lookup = $conn->prepare("SELECT status FROM accounts WHERE id = ? AND entity_type = 'user' LIMIT 1");
$lookup->bind_param('i', $userId);
$lookup->execute();
$user = $lookup->get_result()->fetch_assoc();
$lookup->close();
if (!$user) {
    http_response_code(404);
    exit('User not found.');
}

$newStatus = $user['status'] === 'Blocked' ? 'Active' : 'Blocked';
$update = $conn->prepare("UPDATE accounts SET status = ? WHERE id = ? AND entity_type = 'user'");
$update->bind_param('si', $newStatus, $userId);
$update->execute();
$update->close();

$alert = $newStatus === 'Active' ? 'User status activated' : 'User status blocked';
header('Location: users_profile_list.php?alert=' . urlencode($alert));
exit();
