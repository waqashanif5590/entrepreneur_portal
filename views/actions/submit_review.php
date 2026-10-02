<?php
require_once __DIR__ . '/../../config/database.php';
session_start();
$accountId = (int)($_SESSION['id'] ?? 0);
$roleStatement = $conn->prepare('SELECT entity_type FROM accounts WHERE id = ?');
$roleStatement->bind_param('i', $accountId);
$roleStatement->execute();
$account = $roleStatement->get_result()->fetch_assoc();
$roleStatement->close();
if (empty($_SESSION['loggedin']) || !in_array($account['entity_type'] ?? '', ['agent', 'user'], true)) {
    http_response_code(403);
    exit('Access denied.');
}
if (isset($_GET['business_idea_id']) && isset($_GET['agent_profile_id'])) {
    $rawIdeaId = $_GET['business_idea_id'];
    $rawAgentAccountId = $_GET['agent_profile_id'];
} else {
    http_response_code(400);
    exit('Invalid request');
}
if (!is_string($rawIdeaId) || !preg_match('/\A[0-9]+\z/', $rawIdeaId) || (int)$rawIdeaId < 1 || (string)(int)$rawIdeaId !== ltrim($rawIdeaId, '0') ||
    !is_string($rawAgentAccountId) || !preg_match('/\A[0-9]+\z/', $rawAgentAccountId) || (int)$rawAgentAccountId < 1 || (string)(int)$rawAgentAccountId !== ltrim($rawAgentAccountId, '0')) {
    http_response_code(400);
    exit('Invalid request');
}
$business_idea_id = (int)$rawIdeaId;
$agent_profile_id = (int)$rawAgentAccountId;
?>
<?php
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $review = $_POST['review'];
        $user_id = (int)$_SESSION['id'];
        $sql = "INSERT INTO `reviews` (`comment_content`, `user_id`, `business_idea_id`, `submission_date`) VALUES
         ('$review', '$user_id', '$business_idea_id', current_timestamp())";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $alert = "✅ Review submitted successfully";
            header("Location: ../ideas/idea_details.php?business_idea_id=$business_idea_id&agent_profile_id=$agent_profile_id&alert=" . urlencode($alert));
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
