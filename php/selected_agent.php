<?php
include '../database.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/selected_agent.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'partials/_header.php'; ?>
    <div id="container">
        <?php include 'partials/_sidebar.php'; ?>
        <section id="main_content">

            <h2>Agent / Entrepreneur Profile</h2>
            <p>Detailed information and verification status of the selected agent.</p>

            <?php
            if (isset($_GET['alert'])) {
                $alert = $_GET['alert'];
                echo '<p id="alert_message">' . $alert . '</p>';
                unset($alert);
            }
            ?>
            <div class="profile_card profile_card_admin">
                <?php
                // check whether agent_account_id is set or not
                $agent_account_id = base64_decode($_GET['agent_account_id']);
                $sql = "SELECT * FROM `profiles` WHERE agent_account_id = $agent_account_id";

                $result = mysqli_query($conn, $sql);
                $numRows = mysqli_num_rows($result);
                if ($numRows == 0) {
                    echo '<div class="add_card">
                    <h2>No profile created</h2>
                    <a href="create_profile.php" class="explore_btn">Create Profile</a>
                    </div>';
                }
                // $row = mysqli_fetch_assoc($result);
                while ($row = mysqli_fetch_assoc($result)) {
                    $agent_encoded_id = base64_encode($row["agent_account_id"]);
                    echo '
               
                <div class="profile_header">
                    <div class="profile_info">
                        <div class="profile_pic">
                            <img src="../uploads/profiles/' . $row["profile_image"] . '" alt="Agent Profile">
                        </div>

                        <div class="profile_basic">
                            <h3>' . $row["agent_f_name"] . ' ' . $row["agent_l_name"] . '</h3>
                            <span class="email">' . $row["agent_email"] . '</span>
                            <p class="expertise">' . $row["field_expertise"] . '</p>
                        </div>
                    </div>


                    <span class="status ' . $row["status"] . '">' . $row["status"] . '</span>
                </div>

                <!-- Personal Information -->
                <div class="profile_section">
                    <h4>Personal Information</h4>
                    <div class="info_grid">
                        <p><strong>Full Name: </strong> ' . $row["agent_f_name"] . ' ' . $row["agent_l_name"] . '</p>
                        <p><strong>Email: </strong> ' . $row["agent_email"] . '</p>
                        <p><strong>Phone: </strong> ' . $row["contact"] . '</p>
                        <p><strong>State: </strong> ' . $row["country"] . '</p>
                        <p><strong>City: </strong> ' . $row["city"] . '</p>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="profile_section">
                    <h4>Professional Information</h4>
                    <div class="info_grid">
                        <p><strong>Expertise: </strong>' . $row["field_expertise"] . '</p>
                        <p><strong>Experience: </strong> ' . $row["experience"] . ' Years</p>
                        <p><strong>Organization: </strong>' . $row["org_name"] . '</p>
                        <p><strong>LinkedIn: </strong>' . $row["agent_weblink"] . '</p>
                    </div>
                </div>

                <!-- Documents -->
                <div class="profile_section">
                    <h4>Verification Documents</h4>
                    <div class="documents">
                        <a href="../uploads/profiles/'.$row["cnic"].'" class="doc_btn">CNIC</a>
                        <a href="../uploads/profiles/'.$row["resume"].'" class="doc_btn">Resume</a>
                        <a href="../uploads/profiles/'.$row["certificate"].'" class="doc_btn">Certificates</a>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="profile_section">
                    <h4>Account Information</h4>
                    <div class="info_grid">
                        <p><strong>Agent ID: </strong> 00' . $row["agent_account_id"] . '</p>
                        <p><strong>Status: </strong> ' . $row["status"] . '</p>
                        <p><strong>Registration Date: </strong> ' . $row["creation_date"] . '</p>
                    </div>
                </div>

                <div class="profile_actions">';
                    $logged_id = $_SESSION['id'];
                    $check_type = "SELECT entity_type FROM `accounts` WHERE id = '$logged_id'";
                    $type_result = mysqli_query($conn, $check_type);
                    $type_row = mysqli_fetch_assoc($type_result);
                    if ($type_row['entity_type'] == 'agent') {
                        echo
                        '<a href="./update_profile.php?agent_account_id=' . $agent_encoded_id . '" class="approve_btn">Update Profile</a>
                        <a href="./delete_agent.php?agent_account_id=' . $agent_encoded_id . '" class="block_btn">Delete</a>
                     <a href="./agent_portal.php" class="back_btn">Back to Home</a>
                     </div>';
                    } else if ($type_row['entity_type'] == 'admin') {
                        if ($row["status"] == "Pending") {
                            echo
                            '<a href="./approve_agent.php?agent_account_id=' . $agent_encoded_id . '" class="approve_btn">Approve</a>
                        <a href="./delete_agent.php?agent_account_id=' . $agent_encoded_id . '" class="block_btn">Delete</a>
                     <a href="./agents_profile_list.php" class="back_btn">Back to Agents</a>
                     </div>';
                        } else if ($row["status"] == "Approved") {
                            $agent_account_id = base64_encode($row["agent_account_id"]);
                            echo ' <a href="./block_agent.php?agent_account_id=' . $agent_encoded_id . '" class="block_btn">Block</a>
                         <a href="./delete_agent.php?agent_account_id=' . $agent_encoded_id . '" class="block_btn">Delete</a>
                          <a href="./agents_profile_list.php" class="back_btn">Back to Agents</a>
                </div>';
                        } else if ($row["status"] == "Blocked") {
                            $agent_account_id = base64_encode($row["agent_account_id"]);
                            echo '<a href="./block_agent.php?agent_account_id=' . $agent_encoded_id . '" class="reject_btn">Unblock</a>
                         <a href="./delete_agent.php?agent_account_id=' . $agent_encoded_id . '" class="block_btn">Delete</a>
                          <a href="./agents_profile_list.php" class="back_btn">Back to Agents</a>
                          </div>';
                        } else if ($row["status"] == "Rejected") {
                            $agent_account_id = base64_encode($row["agent_account_id"]);
                            echo ' <a href="./delete_agent.php?agent_account_id=' . $agent_encoded_id . '" class="block_btn">Delete</a>
                        <a href="./block_agent.php?agent_account_id=' . $agent_encoded_id . '" class="block_btn">Block</a>
                          <a href="./agents_profile_list.php" class="back_btn">Back to Agents</a>
                          </div>';
                        }
                    }
                }
                ?>
            </div>

        </section>


    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../JS/siderbar.js"></script>
</body>

</html>