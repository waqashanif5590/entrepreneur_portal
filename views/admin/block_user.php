<?php
require_once __DIR__ . '/../../config/database.php';
session_start();
if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    // Update the user's status to 'Blocked' or 'unblock'
    $sql = "SELECT * FROM `accounts` WHERE `id` = '$userId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    if ($row["status"] == "Blocked") {
        $sql = "UPDATE `accounts` SET `status` = 'Active' WHERE `id` = '$userId'";
        $alert = "User status Activated";
    } else {
        $sql = "UPDATE `accounts` SET `status` = 'Blocked' WHERE `id` = '$userId'";
        $alert = "User status Blocked";
    }
    if (mysqli_query($conn, $sql)) {
        // Redirect back to the users profile list after blocking
        header("Location: users_profile_list.php?alert=$alert");
        exit();
    } else {
        echo "Error updating user status: " . mysqli_error($conn);
    }
} else {
    echo "Invalid user ID.";
}
