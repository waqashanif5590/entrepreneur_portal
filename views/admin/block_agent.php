<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

requireAccountRole($conn, ['admin']);
requirePostCsrfToken();
$agentId = requestPositiveId($_POST['agent_account_id'] ?? null);
if ($agentId === null) {
    http_response_code(400);
    exit('Invalid agent request.');
}

$lookup = $conn->prepare('SELECT status FROM profiles WHERE agent_account_id = ? LIMIT 1');
$lookup->bind_param('i', $agentId);
$lookup->execute();
$agent = $lookup->get_result()->fetch_assoc();
$lookup->close();
if (!$agent) {
    http_response_code(404);
    exit('Agent profile not found.');
}

$newStatus = $agent['status'] === 'Blocked' ? 'Pending' : 'Blocked';
$update = $conn->prepare('UPDATE profiles SET status = ? WHERE agent_account_id = ?');
$update->bind_param('si', $newStatus, $agentId);
$update->execute();
$update->close();

$alert = $newStatus === 'Blocked' ? 'Agent status blocked' : 'Agent status changed to pending';
header('Location: ../profiles/selected_agent.php?agent_account_id=' . $agentId . '&alert=' . urlencode($alert));
exit();
