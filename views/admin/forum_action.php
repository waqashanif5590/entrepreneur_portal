<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

requireAccountRole($conn, ['admin']);
requirePostCsrfToken();
$action = $_POST['action'] ?? '';
$forumId = requestPositiveId($_POST['forum_id'] ?? null);
if (!in_array($action, ['approve', 'delete'], true) || $forumId === null) {
    http_response_code(400);
    exit('Invalid request.');
}

if ($action === 'approve') {
    $statement = $conn->prepare("UPDATE forums SET status = 'Approved' WHERE id = ? AND status = 'Pending'");
    $statement->bind_param('i', $forumId);
    $statement->execute();
    $alert = $statement->affected_rows ? 'Forum was approved' : 'Forum was not pending';
    $statement->close();
} else {
    $conn->begin_transaction();
    $threadStatement = $conn->prepare('DELETE FROM threads WHERE forum_id = ?');
    $threadStatement->bind_param('i', $forumId);
    $threadsDeleted = $threadStatement->execute();
    $threadStatement->close();
    $forumStatement = $conn->prepare('DELETE FROM forums WHERE id = ?');
    $forumStatement->bind_param('i', $forumId);
    $forumDeleted = $forumStatement->execute();
    $forumStatement->close();
    if (!$threadsDeleted || !$forumDeleted) {
        $conn->rollback();
        http_response_code(500);
        exit('Unable to delete forum.');
    }
    $conn->commit();
    $alert = $forumDeleted ? 'Forum was deleted' : 'Forum was not found';
}

header('Location: ../community/forum_list_shared.php?alert=' . urlencode($alert));
exit();
