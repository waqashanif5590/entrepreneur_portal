<?php
require_once __DIR__ . '/../../config/database.php';
session_start();
$agent_account_id = base64_decode($_GET['agent_account_id']);
$sql = "SELECT * FROM `profiles` WHERE agent_account_id='$agent_account_id'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    if ($row["status"] != "Blocked") {
        $sql = "UPDATE `profiles` SET `status` = 'Blocked' WHERE agent_account_id='$agent_account_id'";
        $result = mysqli_query($conn, $sql);
        $agent_account_id = base64_encode($row['agent_account_id']);
        $alert = "Agent status blocked";
        header("Location: ../profiles/selected_agent.php?agent_account_id=$agent_account_id&alert=$alert");
        exit();
    } else {
        $sql = "UPDATE `profiles` SET `status` = 'Pending' WHERE agent_account_id='$agent_account_id'";
        $result = mysqli_query($conn, $sql);
        $agent_account_id = base64_encode($row['agent_account_id']);
        $alert = "Agent status changed to pending";
        header("Location: ../profiles/selected_agent.php?agent_account_id=$agent_account_id&alert=$alert");
        exit();
    }
}
