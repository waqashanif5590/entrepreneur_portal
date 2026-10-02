<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login_dashboard.php");
    exit();
}

$rawAgentAccountId = $_GET['agent_profile_id'] ?? null;
if (!is_string($rawAgentAccountId) || !preg_match('/\A[0-9]+\z/', $rawAgentAccountId) || (int)$rawAgentAccountId < 1 || (string)(int)$rawAgentAccountId !== ltrim($rawAgentAccountId, '0')) {
    http_response_code(400);
    exit('Invalid agent request.');
}
$agent_profile_id = (int)$rawAgentAccountId;
$user_id = $_SESSION['id'];

function redirect_back($agent_profile_id, $alert) {
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if (!empty($ref)) {
        $separator = strpos($ref, '?') !== false ? '&' : '?';
        header('Location: ' . $ref . $separator . 'alert=' . urlencode($alert));
    } else {
        header('Location: ../profiles/selected_agent_ideas.php?agent_profile_id=' . $agent_profile_id . '&alert=' . urlencode($alert));
    }
    exit();
}

// Check if user is already following this agent
$check_sql = "SELECT id FROM user_follows WHERE user_id='$user_id' AND agent_profile_id='$agent_profile_id'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    // User is already following, redirect back with message
    $alert = "You are already following this agent";
    redirect_back($agent_profile_id, $alert);
}

// Add follow to user_follows table
$insert_sql = "INSERT INTO user_follows (user_id, agent_profile_id) VALUES ('$user_id', '$agent_profile_id')";
mysqli_query($conn, $insert_sql);

// Update engagement stats
$sql = "UPDATE engagement_stats
        SET followers = followers + 1
        WHERE agent_profile_id='$agent_profile_id'";

mysqli_query($conn, $sql);
$search_sql = "SELECT * FROM `profiles` WHERE agent_account_id='$agent_profile_id'";
$result = mysqli_query($conn, $search_sql);
$row = mysqli_fetch_assoc($result);
$name = $row['agent_f_name'] . ' ' . $row['agent_l_name'];
$alert = "You are now following $name";
redirect_back($agent_profile_id, $alert);
