<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

$role = requireAccountRole($conn, ['user', 'agent']);
requirePostCsrfToken();
$ideaId = requestPositiveId($_GET['business_idea_id'] ?? null);
$review = trim($_POST['review'] ?? '');
if ($ideaId === null || $review === '') {
    http_response_code(400);
    exit('Invalid review request.');
}

$ideaStatement = $conn->prepare('SELECT user_id, status, visibility FROM business_ideas WHERE id = ? LIMIT 1');
$ideaStatement->bind_param('i', $ideaId);
$ideaStatement->execute();
$idea = $ideaStatement->get_result()->fetch_assoc();
$ideaStatement->close();
$accountId = (int)$_SESSION['id'];
$isOwner = $role === 'agent' && $idea && (int)$idea['user_id'] === $accountId;
$mayReview = $idea && $idea['status'] === 'Approved' &&
    ($idea['visibility'] === 'Public' || ($role === 'agent' && $idea['visibility'] === 'Mentors Only'));
if (!$mayReview || $isOwner) {
    http_response_code(404);
    exit('Idea not found.');
}

$statement = $conn->prepare('INSERT INTO reviews (comment_content, user_id, business_idea_id, submission_date) VALUES (?, ?, ?, CURRENT_TIMESTAMP())');
$statement->bind_param('sii', $review, $accountId, $ideaId);
if (!$statement->execute()) {
    http_response_code(500);
    exit('Unable to submit review.');
}
$statement->close();
header('Location: ../ideas/idea_details.php?business_idea_id=' . $ideaId . '&alert=' . urlencode('Review submitted successfully'));
exit();
