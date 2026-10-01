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
    $business_idea_id = base64_decode($_GET['business_idea_id']);
    $agent_profile_id = base64_decode($_GET['agent_profile_id']);
    if (!is_numeric($business_idea_id) || !is_numeric($agent_profile_id)) {
        die('Invalid request');
    }
}
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
            $business_idea_id = base64_encode($business_idea_id);
            $agent_profile_id = base64_encode($agent_profile_id);
            $alert = "✅ Review submitted successfully";
            header("Location: ../ideas/idea_details.php?business_idea_id=$business_idea_id&agent_profile_id=$agent_profile_id&alert=" . urlencode($alert));
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
