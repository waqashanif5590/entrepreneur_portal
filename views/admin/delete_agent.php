<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

$role = requireAccountRole($conn, ['admin', 'agent']);
requirePostCsrfToken();
$agentId = requestPositiveId($_POST['agent_account_id'] ?? null);
if ($agentId === null) {
    http_response_code(400);
    exit('Invalid request.');
}
$accountId = (int)$_SESSION['id'];
if ($role === 'agent' && $accountId !== $agentId) {
    http_response_code(403);
    exit('Access denied.');
}

$profileStatement = $conn->prepare('SELECT profile_image, cnic, resume, certificate FROM profiles WHERE agent_account_id = ? LIMIT 1');
$profileStatement->bind_param('i', $agentId);
$profileStatement->execute();
$profile = $profileStatement->get_result()->fetch_assoc();
$profileStatement->close();
if (!$profile) {
    http_response_code(404);
    exit('Agent profile not found.');
}
$filesToDelete = [$profile['profile_image'], $profile['cnic'], $profile['resume'], $profile['certificate']];

$conn->begin_transaction();
$deleteIdeas = $conn->prepare('DELETE FROM business_ideas WHERE user_id = ?');
$deleteIdeas->bind_param('i', $agentId);
$ideasDeleted = $deleteIdeas->execute();
$deleteIdeas->close();

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

foreach ($filesToDelete as $file) {
    if (!empty($file)) {
        $fileName = basename($file);
        foreach ([
            dirname(__DIR__, 2) . '/uploads/profiles/' . $fileName,
            dirname(__DIR__, 2) . '/storage/private_profiles/' . $fileName,
        ] as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }
}

$alert = 'Agent profile and associated business ideas deleted successfully.';
if ($role === 'agent') {
    header('Location: ../mentor/agent_portal.php?alert=' . urlencode($alert));
} else {
    header('Location: ../profiles/agent_profiles.php?alert=' . urlencode($alert));
}
exit();
