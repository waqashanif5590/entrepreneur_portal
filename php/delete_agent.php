<?php
session_start();
include '../database.php';
$agent_account_id = base64_decode($_GET['agent_account_id']);

// Fetch file paths before deleting the profile
$sql_fetch = "SELECT profile_image, cnic, resume, certificate FROM `profiles` WHERE agent_account_id=$agent_account_id";
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) > 0) {
    $row = mysqli_fetch_assoc($result_fetch);
    $files_to_delete = [$row['profile_image'], $row['cnic'], $row['resume'], $row['certificate']];
    foreach ($files_to_delete as $file) {
        if (!empty($file)) {
            $file_path = "../uploads/profiles/" . $file;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
}

$sql = "DELETE FROM `profiles` WHERE agent_account_id=$agent_account_id";
$result = mysqli_query($conn, $sql);
if ($result) {
    $sql2 = "DELETE FROM `business_ideas` WHERE agent_profile_id = '$agent_account_id';
    DELETE FROM `forums` WHERE author_id = '$agent_account_id';
    DELETE FROM `engagement_stats` WHERE agent_profile_id='$agent_account_id';
    DELETE FROM `user_likes` WHERE agent_profile_id='$agent_account_id';
    DELETE FROM `user_follows` WHERE agent_profile_id='$agent_account_id';
    DELETE FROM `user_ratings` WHERE agent_profile_id='$agent_account_id';
    DELETE FROM `user_ratings` WHERE agent_profile_id='$agent_account_id';
    "; 
    $result2 = mysqli_query($conn, $sql2);


    if ($result2) {
        $alert = "Agent profile and associated informations deleted successfully.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    $logged_id = $_SESSION['id'];
    $check_type = "SELECT entity_type FROM `accounts` WHERE id='$logged_id'";
    $check_result = mysqli_query($conn, $check_type);
    $check_row = mysqli_fetch_assoc($check_result);
    if ($check_row['entity_type'] === 'agent') {
        $alert = "Agent profile and associated business ideas deleted successfully.";
        header("Location: agent_portal.php?alert=$alert");
        exit();
    } else if ($check_row['entity_type'] === 'admin') {
        $alert = "Agent profile and associated business ideas deleted successfully.";
        header("Location: agents_profile_list.php?alert=$alert");
        exit();
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
