<?php
require_once __DIR__ . '/../../config/database.php';
session_start();
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

$rawIdeaId = $_GET['business_idea_id'] ?? null;
if (!is_string($rawIdeaId) || !preg_match('/\A[0-9]+\z/', $rawIdeaId) || (int)$rawIdeaId < 1 || (string)(int)$rawIdeaId !== ltrim($rawIdeaId, '0')) {
    http_response_code(400);
    exit('Invalid request.');
}
$ideaId = (int)$rawIdeaId;
$statement = $conn->prepare("UPDATE business_ideas SET status = 'Rejected' WHERE id = ? AND status = 'Pending'");
$statement->bind_param('i', $ideaId);
$statement->execute();
$alert = $statement->affected_rows ? 'Idea status rejected' : 'Idea was not pending';
$statement->close();
header('Location: ../ideas/idea_details.php?business_idea_id=' . $ideaId . '&alert=' . urlencode($alert));
exit();
