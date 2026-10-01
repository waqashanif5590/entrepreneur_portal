<?php
session_start();
include '../database.php';
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
    if (isset($_GET['business_idea_id'])) {
        $business_idea_id = base64_decode($_GET['business_idea_id']);
        // $agent_profile_id = base64_decode($_GET['agent_profile_id']);
        if (!is_numeric($business_idea_id)) {
            die('Invalid request');
        }
    }
    $sql = "DELETE FROM business_ideas WHERE `business_ideas`.`idea_id` = '$business_idea_id'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $sql2 = "DELETE FROM reviews WHERE business_idea_id = '$business_idea_id'";
        $result2 = mysqli_query($conn, $sql2);
        if (!$result2) {
            die("Comments were not deleted") . mysqli_error($conn);
        }
        $portal_id = $_SESSION["id"];
        $sql = "SELECT * FROM accounts WHERE id='$portal_id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $entity_type = $row["entity_type"];

        $alert = "✅ Idea deleted successfully";
        if ($entity_type === 'admin') {
            header("Location: ideas_list.php?alert=$alert");
            exit();
        } else if ($entity_type === 'agent') {
            header("Location: agent_portal.php?alert=$alert");
            exit();
        }
    } else {
        echo "Can't be deleted: " . mysqli_error($conn);
    }
}
