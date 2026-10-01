<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login_dashboard.php");
    exit();
}

$agent_profile_id = $_GET['agent_profile_id'];
$user_id = $_SESSION['id'];

function redirect_back($agent_profile_id, $alert) {
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if (!empty($ref)) {
        $separator = strpos($ref, '?') !== false ? '&' : '?';
        header('Location: ' . $ref . $separator . 'alert=' . urlencode($alert));
    } else {
        header('Location: ../profiles/selected_agent_ideas.php?agent_profile_id=' . base64_encode($agent_profile_id) . '&alert=' . urlencode($alert));
    }
    exit();
}

// Check if user has already liked this agent
$check_sql = "SELECT id FROM user_likes WHERE user_id='$user_id' AND agent_profile_id='$agent_profile_id'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    // User has already liked, redirect back with message
    $alert = "You have already liked this agent";
    redirect_back($agent_profile_id, $alert);
}

// Add like to user_likes table
$insert_sql = "INSERT INTO user_likes (user_id, agent_profile_id) VALUES ('$user_id', '$agent_profile_id')";
mysqli_query($conn, $insert_sql);

// Update engagement stats
$sql = "UPDATE engagement_stats
        SET likes = likes + 1
        WHERE agent_profile_id='$agent_profile_id'";

mysqli_query($conn, $sql);
$encoded_id = base64_encode($agent_profile_id);

$search_sql = "SELECT * FROM `profiles` WHERE agent_account_id='$agent_profile_id'";
$result = mysqli_query($conn, $search_sql);
$row = mysqli_fetch_assoc($result);
$name = $row['agent_f_name'] . ' ' . $row['agent_l_name'];
$alert = "You liked $name";
redirect_back($agent_profile_id, $alert);
