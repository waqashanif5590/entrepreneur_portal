<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
requireAccountRole($conn, ['agent']);
$agentAccountId = (int)$_SESSION['id'];
$csrfToken = htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8');
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/agent_portal.css">
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
            <?php
            if (isset($_GET['alert'])) {
                $alert = $_GET['alert'];
                echo '<p id="alert_message">' . $escape($alert) . '</p>';
                unset($alert);
            }
            ?>
            <h2>Welcome to Agent Portal</h2>
            <p>This portal is designed to help agents manage their tasks and resources efficiently. Here you can find
                tools, resources, and support to assist you in your role.</p>
            <div class="search_bar">
                <form action="../search/search_idea_agent.php" method="get">
                    <input type="text" name="q" id="search" placeholder="Search mentors, ideas, templates, resources...">
                    <button id="search_button"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="dashboard">
                <h1>Dashboard</h1>
                <!-- Show existing records -->
                <div class="details_card_container">
                    <div class="details_card">
                        <div class="card_content">
                            <h2>Total Ideas</h2>
                            <p>
                                <?php
                                $agent_profile_id = $agentAccountId;
                                $countStatement = $conn->prepare('SELECT COUNT(*) AS total FROM business_ideas WHERE user_id = ?');
                                $countStatement->bind_param('i', $agent_profile_id);
                                $countStatement->execute();
                                echo (int)$countStatement->get_result()->fetch_assoc()['total'];
                                $countStatement->close();
                                ?>
                            </p>
                        </div>
                        <i class="card-icon fas fa-chart-line"></i>
                    </div>
                    <div class="details_card">
                        <div class="card_content">
                            <h2>Approved Ideas</h2>
                            <p><?php
                                $countStatement = $conn->prepare("SELECT COUNT(*) AS total_approved FROM business_ideas WHERE user_id = ? AND status = 'Approved'");
                                $countStatement->bind_param('i', $agent_profile_id);
                                $countStatement->execute();
                                echo (int)$countStatement->get_result()->fetch_assoc()['total_approved'];
                                $countStatement->close();
                                ?></p>
                        </div>
                        <i class="card-icon fas fa-thumbs-up"></i>
                    </div>
                    <div class="details_card">
                        <div class="card_content">
                            <h2>Rejected Ideas</h2>
                            <p><?php
                                $countStatement = $conn->prepare("SELECT COUNT(*) AS total_approved FROM business_ideas WHERE user_id = ? AND status = 'Rejected'");
                                $countStatement->bind_param('i', $agent_profile_id);
                                $countStatement->execute();
                                echo (int)$countStatement->get_result()->fetch_assoc()['total_approved'];
                                $countStatement->close();
                                ?></p>
                        </div>
                        <i class="card-icon fas fa-thumbs-down"></i>
                    </div>
                    <div class="details_card">
                        <div class="card_content">
                            <h2>Pending Ideas</h2>
                            <p><?php
                                $countStatement = $conn->prepare("SELECT COUNT(*) AS total_approved FROM business_ideas WHERE user_id = ? AND status = 'Pending'");
                                $countStatement->bind_param('i', $agent_profile_id);
                                $countStatement->execute();
                                echo (int)$countStatement->get_result()->fetch_assoc()['total_approved'];
                                $countStatement->close();
                                ?></p>
                        </div>
                        <i class="card-icon fas fa-hourglass-half"></i>
                    </div>
                </div>

                <!-- Add new Business domain card -->
                <h1>Your Business Domains</h1>
                <div class="card_container">
                    <div class="add_card">
                        <h2>Add New Business Domain</h2>
                        <?php
                        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
                            $agent_id = $agentAccountId;
                            $profileStatement = $conn->prepare('SELECT * FROM profiles WHERE agent_account_id = ? LIMIT 1');
                            $profileStatement->bind_param('i', $agent_id);
                            $profileStatement->execute();
                            $row = $profileStatement->get_result()->fetch_assoc();
                            $numRows = $row ? 1 : 0;
                            $profileStatement->close();

                            if ($numRows == 0) {
                                $url = './create_profile.php';
                                echo '<a href="' . $url . '" class="explore_btn"><i class="fas fa-plus"></i></a>';
                            } else {
                                if ($row["status"] == "Pending") {
                                    echo "Your profile is pending for admin approval.";
                                } else if ($row["status"] == "Blocked") {
                                    echo "Your profile is Blocked. Please wait for admin approval.";
                                } else {
                                    $url = './add_business_domain.php';
                                    echo '<a href="' . $url . '" class="explore_btn"><i class="fas fa-plus"></i></a>';
                                }
                            }
                        }
                        ?>
                    </div>
                    <?php
                    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                        $agent_id = $agentAccountId;
                        $profileStatement = $conn->prepare('SELECT 1 FROM profiles WHERE agent_account_id = ? LIMIT 1');
                        $profileStatement->bind_param('i', $agent_id);
                        $profileStatement->execute();
                        $numRows = $profileStatement->get_result()->num_rows;
                        $profileStatement->close();
                        if ($numRows == 0) {
                            echo '<div class="add_card">
                                    <h2 class="display-4">No Business idea found</h2>
                                    <p class="lead">You have not uploaded any business idea yet.</p>
                                </div>';
                        } else {
                            $agent_profile_id = $agentAccountId;
                            $ideasStatement = $conn->prepare('SELECT * FROM business_ideas WHERE user_id = ? ORDER BY id DESC');
                            $ideasStatement->bind_param('i', $agent_profile_id);
                            $ideasStatement->execute();
                            $result = $ideasStatement->get_result();
                            $numRows = $result->num_rows;
                            if ($numRows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $business_idea_id = (int)$row['id'];
                                    echo ' <div class="card">
                                        <h2>' . $escape($row['idea_title']) . '</h2>
                                        <p>' . $escape($row['problem_statement']) . '</p>
                                        <div class="buttons">
                                        <a href="../ideas/idea_details.php?business_idea_id=' . $business_idea_id . '" class="explore_btn">Explore</a>
                                        <a href="./update_idea.php?business_idea_id=' . $business_idea_id . '" class="edit_idea">Edit</a>
                                        <form action="../admin/delete_idea.php" method="post"><input type="hidden" name="csrf_token" value="' . $csrfToken . '"><input type="hidden" name="business_idea_id" value="' . $business_idea_id . '"><button class="delete_idea" type="submit">Delete</button></form>
                                        <p class="status status_' . $escape($row["status"]) . '">' . $escape($row["status"]) . '</p>
                                        </div>
                                        </div>';
                                }
                            } else {
                                    echo  '<div class="add_card">
                                            <h2 class="display-4">No Business idea found</h2>
                                            <p class="lead">You have not uploaded any business idea yet.</p>
                                        </div>';
                            }
                            $ideasStatement->close();
                        }
                    }
                    ?>

                </div>
            </div>
        </section>

    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>
    <script>
        setTimeout(function() {
            var msg = document.getElementById("alert_message");
            if (msg) {
                msg.style.display = "none";
            }
        }, 3000); // 3000 milliseconds = 3 seconds
    </script>
    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>