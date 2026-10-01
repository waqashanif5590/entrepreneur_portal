<?php
require_once __DIR__ . '/../../config/database.php';
session_start();
$agent_account_id = base64_decode($_GET['agent_account_id']);
$sql = "SELECT * FROM `profiles` WHERE agent_account_id='$agent_account_id'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    if ($row["status"] == "Pending") {
        $sql = "UPDATE `profiles` SET `status` = 'Approved' WHERE agent_account_id='$agent_account_id'";
        $result = mysqli_query($conn, $sql);
        $agent_account_id = base64_encode($row['agent_account_id']);
        $alert = "Agent status approved";
        header("Location: ../profiles/selected_agent.php?agent_account_id=$agent_account_id&alert=$alert");
        exit();
    }
}
