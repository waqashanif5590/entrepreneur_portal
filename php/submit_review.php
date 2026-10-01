<?php
include '../database.php';
include './tables/create_comment_table.php';
session_start();
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
        $sender_first_name = $_SESSION['firstname'];
        $sender_last_name = $_SESSION['lastname'];
        $sender_full_name = $sender_first_name . ' ' . $sender_last_name;
        $sql = "INSERT INTO `reviews` (`comment_content`, `sender_name`, `business_idea_id`, `submission_date`) VALUES
         ('$review', '$sender_full_name', '$business_idea_id', current_timestamp())";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $business_idea_id = base64_encode($business_idea_id);
            $agent_profile_id = base64_encode($agent_profile_id);
            $portal_id = $_SESSION["id"];
            $sql = "SELECT * FROM accounts WHERE id='$portal_id'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            $entity_type = $row["entity_type"];

            $alert = "✅ Review submitted successfully";
            if ($entity_type === 'agent') {
                header("Location: idea_details_agent.php?business_idea_id=$business_idea_id&agent_profile_id=$agent_profile_id&alert=$alert");
                exit();
            } else if ($entity_type === 'user') {
                header("Location: idea_details_user.php?business_idea_id=$business_idea_id&agent_profile_id=$agent_profile_id&alert=$alert");
                exit();
            } else if ($entity_type === 'admin') {
                header("Location: idea_details_admin.php?business_idea_id=$business_idea_id&agent_profile_id=$agent_profile_id&alert=$alert");
                exit();
            }
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
