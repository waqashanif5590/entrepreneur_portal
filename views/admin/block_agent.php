<?php
require_once __DIR__ . '/../../config/database.php';
session_start();
$rawAgentAccountId = $_GET['agent_account_id'] ?? null;
if (!is_string($rawAgentAccountId) || !preg_match('/\A[0-9]+\z/', $rawAgentAccountId) || (int)$rawAgentAccountId < 1 || (string)(int)$rawAgentAccountId !== ltrim($rawAgentAccountId, '0')) {
    http_response_code(400);
    exit('Invalid agent request.');
}
$agent_account_id = (int)$rawAgentAccountId;
$sql = "SELECT * FROM `profiles` WHERE agent_account_id='$agent_account_id'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    if ($row["status"] != "Blocked") {
        $sql = "UPDATE `profiles` SET `status` = 'Blocked' WHERE agent_account_id='$agent_account_id'";
        $result = mysqli_query($conn, $sql);
        $agent_account_id = (int)$row['agent_account_id'];
        $alert = "Agent status blocked";
        header("Location: ../profiles/selected_agent.php?agent_account_id=$agent_account_id&alert=$alert");
        exit();
    } else {
        $sql = "UPDATE `profiles` SET `status` = 'Pending' WHERE agent_account_id='$agent_account_id'";
        $result = mysqli_query($conn, $sql);
        $agent_account_id = (int)$row['agent_account_id'];
        $alert = "Agent status changed to pending";
        header("Location: ../profiles/selected_agent.php?agent_account_id=$agent_account_id&alert=$alert");
        exit();
    }
}
