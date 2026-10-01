<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login_dashboard.php");
    exit();
}

$agent_profile_id = $_GET['id'];
$user_rating = $_GET['rating'];
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

// Validate rating
if ($user_rating < 1 || $user_rating > 5) {
    redirect_back($agent_profile_id, 'Invalid rating');
}

// Check if user has already rated this agent
$check_sql = "SELECT id FROM user_ratings WHERE user_id='$user_id' AND agent_profile_id='$agent_profile_id'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    // User has already rated, redirect back with message
    $alert = "You have already rated this agent";
    redirect_back($agent_profile_id, $alert);
}

// Add rating to user_ratings table
$insert_sql = "INSERT INTO user_ratings (user_id, agent_profile_id, rating) VALUES ('$user_id', '$agent_profile_id', '$user_rating')";
mysqli_query($conn, $insert_sql);

// Update engagement stats
$sql = "SELECT rating, total_ratings FROM engagement_stats WHERE agent_profile_id='$agent_profile_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$current_rating = $row['rating'];
$total_ratings = $row['total_ratings'];

// Calculate new average
$new_total = $total_ratings + 1;
$new_rating = (($current_rating * $total_ratings) + $user_rating) / $new_total;

$update = "UPDATE engagement_stats
           SET rating='$new_rating', total_ratings='$new_total'
           WHERE agent_profile_id='$agent_profile_id'";

mysqli_query($conn, $update);

$alert = "Thank you for rating!";
redirect_back($agent_profile_id, $alert);
