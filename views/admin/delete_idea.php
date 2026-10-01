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
$role = $account['entity_type'] ?? '';
if (!in_array($role, ['admin', 'agent'], true)) {
    http_response_code(403);
    exit('Access denied.');
}

$ideaId = base64_decode($_GET['business_idea_id'] ?? '', true);
if ($ideaId === false || !ctype_digit($ideaId)) {
    http_response_code(400);
    exit('Invalid request.');
}

$ideaStatement = $conn->prepare('SELECT user_id FROM business_ideas WHERE id = ? LIMIT 1');
$ideaStatement->bind_param('i', $ideaId);
$ideaStatement->execute();
$idea = $ideaStatement->get_result()->fetch_assoc();
$ideaStatement->close();
if (!$idea || ($role === 'agent' && (int)$idea['user_id'] !== $accountId)) {
    http_response_code(403);
    exit('Access denied.');
}

$conn->begin_transaction();
$reviewsStatement = $conn->prepare('DELETE FROM reviews WHERE business_idea_id = ?');
$reviewsStatement->bind_param('i', $ideaId);
$ideaDeleteStatement = $conn->prepare('DELETE FROM business_ideas WHERE id = ?');
$ideaDeleteStatement->bind_param('i', $ideaId);
if (!$reviewsStatement->execute() || !$ideaDeleteStatement->execute()) {
    $conn->rollback();
    http_response_code(500);
    exit('Unable to delete idea.');
}
$conn->commit();
$reviewsStatement->close();
$ideaDeleteStatement->close();

$alert = 'Idea deleted successfully';
if ($role === 'admin') {
    header('Location: ../ideas/ideas_list.php?alert=' . urlencode($alert));
} else {
    header('Location: ../mentor/agent_portal.php?alert=' . urlencode($alert));
}
exit();
