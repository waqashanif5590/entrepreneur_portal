<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

requireAccountRole($conn, ['admin']);
requirePostCsrfToken();
$ideaId = requestPositiveId($_POST['business_idea_id'] ?? null);
if ($ideaId === null) {
    http_response_code(400);
    exit('Invalid request.');
}

$statement = $conn->prepare("UPDATE business_ideas SET status = 'Approved' WHERE id = ? AND status = 'Pending'");
$statement->bind_param('i', $ideaId);
$statement->execute();
$alert = $statement->affected_rows ? 'Idea status approved' : 'Idea was not pending';
$statement->close();
header('Location: ../ideas/idea_details.php?business_idea_id=' . $ideaId . '&alert=' . urlencode($alert));
exit();
