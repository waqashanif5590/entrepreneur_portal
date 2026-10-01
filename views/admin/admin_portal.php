<?php
require_once __DIR__ . '/../../config/database.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/admin_portal.css">
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
                echo '<p id="alert_message">' . $alert . '</p>';
                unset($alert);
            }
            ?>
            <h2>Welcome to Admin Portal</h2>
            <p>This portal is designed for administrators to manage users, agents, and content efficiently.</p>
            <div class="dashboard">
                <?php
                $sql = "SELECT * FROM `accounts` WHERE `entity_type` = 'user'";
                $result = mysqli_query($conn, $sql);
                $numRows = mysqli_num_rows($result);
                echo '<a href="./users_profile_list.php" class="card users_card">
                    <div class="card_content">
                        <h2>Total Users</h2>
                        <p>' . $numRows . ' </p>
                    </div>
                    <i class="card-icon fas fa-users"></i>
                </a>';
                ?>
                <!-- <a href="./users_profile_list.php" class="card users_card">
                    <div class="card_content">
                        <h2>Total Users</h2>
                        <p>135</p>
                    </div>
                    <i class="card-icon fas fa-users"></i>
                </a> -->
                <?php
                $sql = "SELECT COUNT(*) AS total_agents FROM `profiles`";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                $numRows = $row["total_agents"];
                echo '<a href="../profiles/agent_profiles.php" class="card users_card">
                    <div class="card_content">
                        <h2>Total Agents</h2> 
                        <p>' . $numRows . ' </p>
                    </div>
                    <i class="card-icon fas fa-address-card"></i>
                </a>';
                ?>
                <!-- <a href="../profiles/agent_profiles.php" class="card users_card">
                    <div class="card_content">
                        <h2>Total Agents</h2>
                        <p>135</p>
                    </div>
                    <i class="card-icon fas fa-users"></i>
                </a> -->
                <?php
                $sql = "SELECT * FROM `business_ideas`";
                $result = mysqli_query($conn, $sql);
                $numRows = mysqli_num_rows($result);
                echo '<a href="../ideas/ideas_list.php" class="card users_card">
                    <div class="card_content">
                        <h2>Total Ideas</h2>
                        <p>' . $numRows . ' </p>
                    </div>
                    <i class="card-icon fas fa-chart-line"></i>
                </a>';
                ?>
                <!-- <a href="../ideas/ideas_list.php" class="card agent_card">
                    <div class="card_content">
                        <h2>Total Business Ideas</h2>
                        <p>350</p>
                    </div>
                    <i class="card-icon fas fa-user-tie"></i>
                </a> -->
                <?php
                $sql = "SELECT * FROM `business_ideas` WHERE `status` = 'Pending'";
                $result = mysqli_query($conn, $sql);
                $numRows = mysqli_num_rows($result);
                $row = mysqli_fetch_assoc($result);
                if ($row) {
                    $status = $row["status"];
                } else {
                    $status = "Pending";
                }
                echo '<a href="../ideas/ideas_list.php?status=' . $status . '" class="card users_card">
                    <div class="card_content">
                        <h2>Pending Idea Requests</h2>
                        <p>' . $numRows . ' </p>
                    </div>
                    <i class="card-icon fas fa-hourglass-half"></i>
                </a>';
                ?>
                <?php
                $sql2 = "SELECT * FROM `profiles` WHERE `status` = 'Pending'";
                $result2 = mysqli_query($conn, $sql2);
                $numRows = mysqli_num_rows($result2);
                $row2 = mysqli_fetch_assoc($result2);
                if ($row2) {
                    $status = $row2["status"];
                } else {
                    $status = "Pending";
                }
                echo '<a href="../profiles/agent_profiles.php?status=' . $status . '" class="card users_card">
                    <div class="card_content">
                        <h2>Agents Pending Profiles</h2> 
                        <p>' . $numRows . ' </p>
                    </div>
                    <i class="card-icon fas fa-hourglass-half"></i>
                </a>';
                ?>
                <!-- <a href="" class="card">
                    <div class="card_content">
                        <h2>Pending Requests</h2>
                        <p>12</p>
                    </div>
                    <i class="card-icon fas fa-user-check"></i>
                </a> -->

                <?php
                $sql2 = "SELECT * FROM `forums` WHERE `status` = 'Pending'";
                $result2 = mysqli_query($conn, $sql2);
                $numRows = mysqli_num_rows($result2);
                $row2 = mysqli_fetch_assoc($result2);
                if ($row2) {
                    $status = $row2["status"];
                } else {
                    $status = "Pending";
                }
                echo ' <a href="../community/forum_list_shared.php?status=' . $status . '" class="card">
                    <div class="card_content">
                        <h2>New Forums</h2>
                        <p>' . $numRows . '</p>
                    </div>
                   <i class="card-icon fas fa-clipboard-question"></i>
                </a>';
                ?>
                <?php
                $sql2 = "SELECT * FROM `forums`";
                $result2 = mysqli_query($conn, $sql2);
                $numRows = mysqli_num_rows($result2);
                $row2 = mysqli_fetch_assoc($result2);
                if ($row2) {
                    $status = $row2["status"];
                } else {
                    $status = "Pending";
                }
                echo ' <a href="../community/forum_list_shared.php" class="card">
                    <div class="card_content">
                        <h2>All Forums</h2>
                        <p>' . $numRows . '</p>
                    </div>
                    <i class="card-icon fas fa-table"></i>
                </a>';
                ?>
                <!-- <a href="" class="card">
                    <div class="card_content">
                        <h2>New Forums</h2>
                        <p>8</p>
                    </div>
                    <i class="card-icon fas fa-envelope"></i>
                </a> -->
                <!-- <a href="" class="card">
                    <div class="card_content">
                        <h2>Content Items</h2>
                        <p>150</p>
                    </div>
                    <i class="card-icon fas fa-folder"></i>
                </a> -->

        </section>

    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>