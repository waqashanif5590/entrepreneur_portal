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

$statement = $conn->prepare("UPDATE profiles SET status = 'Approved' WHERE agent_account_id = ? AND status = 'Pending'");
$statement->bind_param('i', $agentId);
$statement->execute();
$alert = $statement->affected_rows ? 'Agent status approved' : 'Agent was not pending';
$statement->close();
header('Location: ../profiles/selected_agent.php?agent_account_id=' . $agentId . '&alert=' . urlencode($alert));
exit();
