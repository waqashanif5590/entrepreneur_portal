<?php
include '../database.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profile List</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/all_agents.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'partials/_header.php'; ?>

    <div id="container">
        <?php include 'partials/_sidebar.php'; ?>

        <section id="main_content">
            <h2>Agents / Entrepreneurs</h2>
            <p>Manage all registered, Pending, and Blocked agents on the platform.</p>

            <!-- Search Box -->
            <div class="search_bar">
                <form action="search_mentors.php" method="get">
                    <input type="text" name="q" id="search" placeholder="Search mentors by name, email or expertise...">
                    <button id="search_button"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <!-- Agents Table -->
            <div class="table_container">
                <table>
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Expertise</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($_GET['status']) && !empty($_GET['status'])) {
                            $status = mysqli_real_escape_string($conn, $_GET['status']);
                            $sql = "SELECT * FROM `profiles` WHERE `status` = '$status' ORDER BY agent_account_id DESC";
                        } else {
                            $sql = "SELECT * FROM `profiles` ORDER BY agent_account_id DESC";
                        }

                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $agent_id = (int)$row['agent_account_id'];
                                $encoded_id = base64_encode($agent_id);   // Correct encoding

                                echo '<tr>
                                    <td>
                                        <img src="../uploads/profiles/' . htmlspecialchars($row['profile_image']) . '" 
                                             class="table_profile" alt="Profile" 
                                             onerror="this.src=\'../images/default-profile.png\';">
                                    </td>
                                    <td>' . htmlspecialchars($row['agent_f_name'] . ' ' . $row['agent_l_name']) . '</td>
                                    <td>' . htmlspecialchars($row['agent_email']) . '</td>
                                    <td><span class="badge ' . htmlspecialchars($row['status']) . '">' .
                                    htmlspecialchars($row['status']) . '</span></td>
                                    <td>' . htmlspecialchars($row['field_expertise']) . '</td>
                                    <td>
                                        <a href="./selected_agent.php?agent_account_id=' . $encoded_id . '">View</a>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6">No profile found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section id="footer">
        <p>© 2025 BizLaunchHub. All Rights Reserved.</p>
    </section>

    <script src="../JS/siderbar.js"></script>
</body>

</html>