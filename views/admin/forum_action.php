<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
if (empty($_SESSION['loggedin']) || empty($_SESSION['id'])) {
    http_response_code(403);
    exit('Access denied.');
}

 $accountId = (int)$_SESSION['id'];
$roleStatement = $conn->prepare('SELECT entity_type FROM accounts WHERE id = ?');
$roleStatement->bind_param('i', $accountId);
$roleStatement->execute();
$account = $roleStatement->get_result()->fetch_assoc();
$roleStatement->close();
if (($account['entity_type'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

$action = $_GET['action'] ?? '';
$forumId = base64_decode($_GET['forum_id'] ?? '', true);
if (!in_array($action, ['approve', 'delete'], true) || $forumId === false || !ctype_digit($forumId)) {
    http_response_code(400);
    exit('Invalid request.');
}

if ($action === 'approve') {
    $statement = $conn->prepare("UPDATE forums SET status = 'Approved' WHERE id = ? AND status = 'Pending'");
    $statement->bind_param('i', $forumId);
    $statement->execute();
    $alert = $statement->affected_rows ? 'Forum was approved' : 'Forum was not pending';
} else {
    $conn->begin_transaction();
    $threadStatement = $conn->prepare('DELETE FROM threads WHERE forum_id = ?');
    $threadStatement->bind_param('i', $forumId);
    $threadStatement->execute();
    $forumStatement = $conn->prepare('DELETE FROM forums WHERE id = ?');
    $forumStatement->bind_param('i', $forumId);
    $forumStatement->execute();
    $conn->commit();
    $alert = $forumStatement->affected_rows ? 'Forum was deleted' : 'Forum was not found';
    $threadStatement->close();
    $forumStatement->close();
}

if (isset($statement)) {
    $statement->close();
}
header('Location: ../community/forum_list_shared.php?alert=' . urlencode($alert));
exit();
