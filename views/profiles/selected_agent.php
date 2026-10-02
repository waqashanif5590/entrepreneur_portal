<?php
require_once __DIR__ . '/../../config/database.php';
session_start();
require_once __DIR__ . '/../../config/security.php';
$viewerRole = requireAccountRole($conn, ['admin', 'agent']);
$csrfToken = csrfToken();
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/selected_agent.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../components/_header.php'; ?>
    <div id="container">
        <?php include __DIR__ . '/../../components/_sidebar.php'; ?>
        <section id="main_content">

            <h2>Agent / Entrepreneur Profile</h2>
            <p>Detailed information and verification status of the selected agent.</p>

            <?php
            if (isset($_GET['alert'])) {
                $alert = $_GET['alert'];
                echo '<p id="alert_message">' . htmlspecialchars($alert, ENT_QUOTES, 'UTF-8') . '</p>';
                unset($alert);
            }
            ?>
            <div class="profile_card profile_card_admin">
                <?php
                $agent_account_id = requestPositiveId($_GET['agent_account_id'] ?? null);
                if ($agent_account_id === null) {
                    http_response_code(400);
                    exit('Invalid agent request.');
                }
                if ($viewerRole === 'agent' && (int)$_SESSION['id'] !== $agent_account_id) {
                    http_response_code(403);
                    exit('Access denied.');
                }
                $profileStatement = $conn->prepare('SELECT * FROM profiles WHERE agent_account_id = ? LIMIT 1');
                $profileStatement->bind_param('i', $agent_account_id);
                $profileStatement->execute();
                $result = $profileStatement->get_result();
                $numRows = $result->num_rows;
                if ($numRows === 0) {
                    http_response_code(404);
                    exit('Agent profile not found.');
                }
                // $row = mysqli_fetch_assoc($result);
                while ($row = mysqli_fetch_assoc($result)) {
                    $agent_account_id = (int)$row["agent_account_id"];
                    echo '
               
                <div class="profile_header">
                    <div class="profile_info">
                        <div class="profile_pic">
                            <img src="profile_image.php?agent_account_id=' . $agent_account_id . '" alt="Agent Profile">
                        </div>

                        <div class="profile_basic">
                            <h3>' . $escape($row["agent_f_name"]) . ' ' . $escape($row["agent_l_name"]) . '</h3>
                            <span class="email">' . $escape($row["agent_email"]) . '</span>
                            <p class="expertise">' . $escape($row["field_expertise"]) . '</p>
                        </div>
                    </div>


                    <span class="status ' . $escape($row["status"]) . '">' . $escape($row["status"]) . '</span>
                </div>

                <!-- Personal Information -->
                <div class="profile_section">
                    <h4>Personal Information</h4>
                    <div class="info_grid">
                        <p><strong>Full Name: </strong> ' . $escape($row["agent_f_name"]) . ' ' . $escape($row["agent_l_name"]) . '</p>
                        <p><strong>Email: </strong> ' . $escape($row["agent_email"]) . '</p>
                        <p><strong>Phone: </strong> ' . $escape($row["contact"]) . '</p>
                        <p><strong>State: </strong> ' . $escape($row["country"]) . '</p>
                        <p><strong>City: </strong> ' . $escape($row["city"]) . '</p>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="profile_section">
                    <h4>Professional Information</h4>
                    <div class="info_grid">
                        <p><strong>Expertise: </strong>' . $escape($row["field_expertise"]) . '</p>
                        <p><strong>Experience: </strong> ' . (int)$row["experience"] . ' Years</p>
                        <p><strong>Organization: </strong>' . $escape($row["org_name"]) . '</p>
                        <p><strong>LinkedIn: </strong>' . $escape($row["agent_weblink"]) . '</p>
                    </div>
                </div>

                <!-- Documents -->
                <div class="profile_section">
                    <h4>Verification Documents</h4>
                    <div class="documents">
                        <a href="download_document.php?agent_account_id='.$agent_account_id.'&document=cnic" class="doc_btn">CNIC</a>
                        <a href="download_document.php?agent_account_id='.$agent_account_id.'&document=resume" class="doc_btn">Resume</a>
                        <a href="download_document.php?agent_account_id='.$agent_account_id.'&document=certificate" class="doc_btn">Certificates</a>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="profile_section">
                    <h4>Account Information</h4>
                    <div class="info_grid">
                        <p><strong>Agent ID: </strong> 00' . $row["agent_account_id"] . '</p>
                        <p><strong>Status: </strong> ' . $escape($row["status"]) . '</p>
                        <p><strong>Registration Date: </strong> ' . $escape($row["creation_date"]) . '</p>
                    </div>
                </div>

                <div class="profile_actions">';
                    if ($viewerRole === 'agent') {
                        echo
                                '<a href="../mentor/update_profile.php?agent_account_id=' . $agent_account_id . '" class="approve_btn">Update Profile</a>
                                <form action="../admin/delete_agent.php" method="post"><input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="agent_account_id" value="' . $agent_account_id . '"><button class="block_btn" type="submit">Delete</button></form>
                            <a href="../mentor/agent_portal.php" class="back_btn">Back to Home</a>
                     </div>';
                    } elseif ($viewerRole === 'admin') {
                        if ($row["status"] == "Pending") {
                            echo
                            '<form action="../admin/approve_agent.php" method="post"><input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="agent_account_id" value="' . $agent_account_id . '"><button class="approve_btn" type="submit">Approve</button></form>
                        <form action="../admin/delete_agent.php" method="post"><input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="agent_account_id" value="' . $agent_account_id . '"><button class="block_btn" type="submit">Delete</button></form>
                     <a href="./agent_profiles.php" class="back_btn">Back to Agents</a>
                     </div>';
                        } else if ($row["status"] == "Approved") {
                                     echo ' <form action="../admin/block_agent.php" method="post"><input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="agent_account_id" value="' . $agent_account_id . '"><button class="block_btn" type="submit">Block</button></form>
                                 <form action="../admin/delete_agent.php" method="post"><input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="agent_account_id" value="' . $agent_account_id . '"><button class="block_btn" type="submit">Delete</button></form>
                          <a href="./agent_profiles.php" class="back_btn">Back to Agents</a>
                </div>';
                        } else if ($row["status"] == "Blocked") {
                                     echo '<form action="../admin/block_agent.php" method="post"><input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="agent_account_id" value="' . $agent_account_id . '"><button class="reject_btn" type="submit">Unblock</button></form>
                                 <form action="../admin/delete_agent.php" method="post"><input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="agent_account_id" value="' . $agent_account_id . '"><button class="block_btn" type="submit">Delete</button></form>
                          <a href="./agent_profiles.php" class="back_btn">Back to Agents</a>
                          </div>';
                        } else if ($row["status"] == "Rejected") {
                            echo ' <form action="../admin/delete_agent.php" method="post"><input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="agent_account_id" value="' . $agent_account_id . '"><button class="block_btn" type="submit">Delete</button></form>
                        <form action="../admin/block_agent.php" method="post"><input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="agent_account_id" value="' . $agent_account_id . '"><button class="block_btn" type="submit">Block</button></form>
                          <a href="./agent_profiles.php" class="back_btn">Back to Agents</a>
                          </div>';
                        }
                        $profileStatement->close();
                    }
                }
                ?>
            </div>

        </section>


    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>