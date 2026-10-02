<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

requireAccountRole($conn, ['user', 'agent']);
requirePostCsrfToken();
$agentId = requestPositiveId($_POST['agent_profile_id'] ?? null);
$rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
$userId = (int)$_SESSION['id'];
if ($agentId === null || $agentId === $userId || $rating === false) {
    http_response_code(400);
    exit('Invalid rating request.');
}

$profile = $conn->prepare("SELECT 1 FROM profiles WHERE agent_account_id = ? AND status = 'Approved' LIMIT 1");
$profile->bind_param('i', $agentId);
$profile->execute();
$isApproved = $profile->get_result()->num_rows > 0;
$profile->close();
if (!$isApproved) {
    http_response_code(404);
    exit('Agent not found.');
}

$check = $conn->prepare('SELECT id FROM user_ratings WHERE user_id = ? AND agent_profile_id = ? LIMIT 1');
$check->bind_param('ii', $userId, $agentId);
$check->execute();
$alreadyRated = $check->get_result()->num_rows > 0;
$check->close();
if ($alreadyRated) {
    header('Location: ../profiles/selected_agent_ideas.php?agent_profile_id=' . $agentId . '&alert=' . urlencode('You have already rated this agent'));
    exit();
}

$stats = $conn->prepare('SELECT rating, total_ratings FROM engagement_stats WHERE agent_profile_id = ? LIMIT 1');
$stats->bind_param('i', $agentId);
$stats->execute();
$currentStats = $stats->get_result()->fetch_assoc();
$stats->close();
if (!$currentStats) {
    http_response_code(404);
    exit('Agent statistics not found.');
}

$newTotal = (int)$currentStats['total_ratings'] + 1;
$newRating = (((float)$currentStats['rating'] * (int)$currentStats['total_ratings']) + $rating) / $newTotal;
$conn->begin_transaction();
$insert = $conn->prepare('INSERT INTO user_ratings (user_id, agent_profile_id, rating) VALUES (?, ?, ?)');
$insert->bind_param('iii', $userId, $agentId, $rating);
$ratingInserted = $insert->execute();
$insert->close();
$update = $conn->prepare('UPDATE engagement_stats SET rating = ?, total_ratings = ? WHERE agent_profile_id = ?');
$update->bind_param('dii', $newRating, $newTotal, $agentId);
$statsUpdated = $update->execute();
$update->close();
if (!$ratingInserted || !$statsUpdated) {
    $conn->rollback();
    http_response_code(500);
    exit('Unable to save rating.');
}
$conn->commit();

header('Location: ../profiles/selected_agent_ideas.php?agent_profile_id=' . $agentId . '&alert=' . urlencode('Thank you for rating!'));
exit();
