<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

requireAccountRole($conn, ['user', 'agent']);
requirePostCsrfToken();
$agentId = requestPositiveId($_POST['agent_profile_id'] ?? null);
$userId = (int)$_SESSION['id'];
if ($agentId === null || $agentId === $userId) {
    http_response_code(400);
    exit('Invalid agent request.');
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

$check = $conn->prepare('SELECT id FROM user_likes WHERE user_id = ? AND agent_profile_id = ? LIMIT 1');
$check->bind_param('ii', $userId, $agentId);
$check->execute();
$alreadyLiked = $check->get_result()->num_rows > 0;
$check->close();
if ($alreadyLiked) {
    header('Location: ../profiles/selected_agent_ideas.php?agent_profile_id=' . $agentId . '&alert=' . urlencode('You have already liked this agent'));
    exit();
}

$insert = $conn->prepare('INSERT INTO user_likes (user_id, agent_profile_id) VALUES (?, ?)');
$insert->bind_param('ii', $userId, $agentId);
if (!$insert->execute()) {
    http_response_code(500);
    exit('Unable to like this agent.');
}
$insert->close();
$update = $conn->prepare('UPDATE engagement_stats SET likes = likes + 1 WHERE agent_profile_id = ?');
$update->bind_param('i', $agentId);
$update->execute();
$update->close();
header('Location: ../profiles/selected_agent_ideas.php?agent_profile_id=' . $agentId . '&alert=' . urlencode('You liked this agent'));
exit();
