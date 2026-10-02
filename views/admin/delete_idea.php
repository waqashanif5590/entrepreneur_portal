<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

$role = requireAccountRole($conn, ['admin', 'agent']);
requirePostCsrfToken();
$ideaId = requestPositiveId($_POST['business_idea_id'] ?? null);
if ($ideaId === null) {
    http_response_code(400);
    exit('Invalid request.');
}
$accountId = (int)$_SESSION['id'];

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
$reviewsDeleted = $reviewsStatement->execute();
$reviewsStatement->close();
$ideaDeleteStatement = $conn->prepare('DELETE FROM business_ideas WHERE id = ?');
$ideaDeleteStatement->bind_param('i', $ideaId);
$ideaDeleted = $ideaDeleteStatement->execute();
$ideaDeleteStatement->close();
if (!$reviewsDeleted || !$ideaDeleted) {
    $conn->rollback();
    http_response_code(500);
    exit('Unable to delete idea.');
}
$conn->commit();

$alert = 'Idea deleted successfully';
header('Location: ' . ($role === 'admin' ? '../ideas/ideas_list.php' : '../mentor/agent_portal.php') . '?alert=' . urlencode($alert));
exit();
