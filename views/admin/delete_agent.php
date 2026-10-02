<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
$rawAgentAccountId = $_GET['agent_account_id'] ?? null;
if (!is_string($rawAgentAccountId) || !preg_match('/\A[0-9]+\z/', $rawAgentAccountId) || (int)$rawAgentAccountId < 1 || (string)(int)$rawAgentAccountId !== ltrim($rawAgentAccountId, '0')) {
    http_response_code(400);
    exit('Invalid request.');
}
$agent_account_id = (int)$rawAgentAccountId;

// Fetch file paths before deleting the profile
$sql_fetch = "SELECT profile_image, cnic, resume, certificate FROM `profiles` WHERE agent_account_id=" . (int)$agent_account_id;
$result_fetch = mysqli_query($conn, $sql_fetch);
$files_to_delete = [];
if ($result_fetch && mysqli_num_rows($result_fetch) > 0) {
    $row = mysqli_fetch_assoc($result_fetch);
    $files_to_delete = [$row['profile_image'], $row['cnic'], $row['resume'], $row['certificate']];
}

$agentId = (int)$agent_account_id;
$conn->begin_transaction();
$deleteContent = $conn->prepare('DELETE FROM business_ideas WHERE user_id = ?');
$deleteContent->bind_param('i', $agentId);
$ideasDeleted = $deleteContent->execute();
$deleteContent->close();

$deleteForums = $conn->prepare('DELETE FROM forums WHERE user_id = ?');
$deleteForums->bind_param('i', $agentId);
$forumsDeleted = $deleteForums->execute();
$deleteForums->close();

$deleteProfile = $conn->prepare('DELETE FROM profiles WHERE agent_account_id = ?');
$deleteProfile->bind_param('i', $agentId);
$profileDeleted = $deleteProfile->execute();
$deleteProfile->close();

if (!$ideasDeleted || !$forumsDeleted || !$profileDeleted) {
    $conn->rollback();
    http_response_code(500);
    exit('Unable to delete the agent profile.');
}
$conn->commit();

foreach ($files_to_delete as $file) {
    if (!empty($file)) {
        $file_path = dirname(__DIR__, 2) . '/uploads/profiles/' . basename($file);
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
}

$logged_id = (int)$_SESSION['id'];
$check_type = "SELECT entity_type FROM `accounts` WHERE id='$logged_id'";
$check_result = mysqli_query($conn, $check_type);
$check_row = mysqli_fetch_assoc($check_result);
$alert = "Agent profile and associated business ideas deleted successfully.";
if ($check_row['entity_type'] === 'agent') {
    header("Location: ../mentor/agent_portal.php?alert=" . urlencode($alert));
    exit();
} elseif ($check_row['entity_type'] === 'admin') {
    header("Location: ../profiles/agent_profiles.php?alert=" . urlencode($alert));
    exit();
}
