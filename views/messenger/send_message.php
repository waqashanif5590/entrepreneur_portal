<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

$role = requireAccountRole($conn, ['user', 'agent']);
requirePostCsrfToken();
$myId = (int)$_SESSION['id'];
$recipientId = requestPositiveId($_POST['chat_with'] ?? null);
$message = trim($_POST['message'] ?? '');
if ($recipientId === null || $message === '' || !canAccessConversation($conn, $myId, $role, $recipientId)) {
    http_response_code(400);
    exit('Invalid message request.');
}

$statement = $conn->prepare('INSERT INTO messages (message_text, sender_id, receiver_id) VALUES (?, ?, ?)');
$statement->bind_param('sii', $message, $myId, $recipientId);
if (!$statement->execute()) {
    http_response_code(500);
    exit('Unable to send message.');
}
$statement->close();
http_response_code(204);
