<?php
include '../database.php';
session_start();
$business_idea_id = base64_decode($_GET['business_idea_id']);
$sql = "SELECT * FROM `business_ideas` WHERE idea_id='$business_idea_id'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    if ($row["status"] == "Pending") {
        $sql = "UPDATE `business_ideas` SET `status` = 'Rejected' WHERE idea_id='$business_idea_id'";
        $result = mysqli_query($conn, $sql);
        $agent_profile_id = base64_encode($row['agent_profile_id']);
        $business_idea_id = base64_encode($row['idea_id']);
        if ($result) {
            $alert = "Idea status rejected";
            header("Location: idea_details_admin.php?agent_profile_id=$agent_profile_id&business_idea_id=$business_idea_id&alert=$alert");
            exit();
        } else {
            echo "Query Failed! " . mysqli_error($conn);
        }
    }
}
