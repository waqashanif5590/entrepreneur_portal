<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Idea list</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/all_agents.css">
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
            <h2>All Ideas</h2>
            <p>Manage all Approved, Pending, and Blocked ideas on the platform.</p>

            <!-- Search Box -->
            <div class="search_bar">
                <form action="../search/search_ideas.php" method="get">
                    <input type="text" name="q" id="search" placeholder="Search mentors, ideas, templates, resources...">
                    <button id="search_button"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <!-- Status Filter -->
            <!-- <div class="filter_box">
                <label>
                    <input type="radio" name="agent_status" checked>
                    <span>All</span>
                </label>

                <label>
                    <input type="radio" name="agent_status">
                    <span class="Approved">Approved</span>
                </label>

                <label>
                    <input type="radio" name="agent_status">
                    <span class="Pending">Pending</span>
                </label>

                <label>
                    <input type="radio" name="agent_status">
                    <span class="Blocked">Blocked</span>
                </label>
            </div> -->
            <!-- Agents Table -->
            <div class="table_container">
                <table>
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Full Name</th>
                            <th>Idea Title</th>
                            <th>Expertise</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        if (isset($_GET["status"])) {
                            $status = "Pending";
                            $sql = "SELECT * FROM `business_ideas` WHERE `status`='Pending'";
                            $result = mysqli_query($conn, $sql);
                            $numRows = mysqli_num_rows($result);
                            if ($numRows == 0) {
                                echo " <tr>
                                    <td colspan='6'>No Pending Idea found.</td>
                                   </tr>";
                            } else {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $agent_account_id = $row["user_id"];
                                    $business_idea_id = $row["id"];
                                    $sql2 = "SELECT * FROM `profiles` WHERE agent_account_id='$agent_account_id'";
                                    $result2 = mysqli_query($conn, $sql2);
                                    while ($row2 = mysqli_fetch_assoc($result2)) {
                                        $agent_name = $row2['agent_f_name'] . ' ' . $row2['agent_l_name'];
                                        $expertise = $row2['field_expertise'];
                                        $profile_image = $row2['profile_image'];
                                    }
                                    $agentAccountId = (int)$agent_account_id;
                                    $ideaId = (int)$business_idea_id;
                                    echo '<tr>
                            <td><img src="../../uploads/profiles/' . $profile_image . '" class="table_profile"></td>
                            <td>' . $agent_name . '</td>
                            <td><span>' . $row["idea_title"] . '</span></td>
                            <td>' . $expertise . '</td>
                            <td><span class="badge ' . $row["status"] . '">' . $row["status"] . '</span></td>
                            <td><a href="./idea_details.php?agent_profile_id=' . $agentAccountId . '&business_idea_id=' . $ideaId . '">View</a></td>
                        </tr>';
                                }
                            }
                        } else {
                            $sql = "SELECT * FROM `business_ideas`";
                            $result = mysqli_query($conn, $sql);
                            $numRows = mysqli_num_rows($result);
                            if ($numRows == 0) {
                                echo " <tr>
                                    <td colspan='6'>No Pending Idea found.</td>
                                   </tr>";
                            } else {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $agent_account_id = $row["user_id"];
                                    $business_idea_id = $row["id"];
                                    $sql2 = "SELECT * FROM `profiles` WHERE agent_account_id='$agent_account_id'";
                                    $result2 = mysqli_query($conn, $sql2);
                                    while ($row2 = mysqli_fetch_assoc($result2)) {
                                        $agent_name = $row2['agent_f_name'] . ' ' . $row2['agent_l_name'];
                                        $expertise = $row2['field_expertise'];
                                        $profile_image = $row2['profile_image'];
                                    }
                                    $agentAccountId = (int)$agent_account_id;
                                    $ideaId = (int)$business_idea_id;
                                    echo '<tr>
                            <td><img src="../../uploads/profiles/' . $profile_image . '" class="table_profile"></td>
                            <td>' . $agent_name . '</td>
                            <td><span>' . $row["idea_title"] . '</span></td>
                            <td>' . $expertise . '</td>
                            <td><span class="badge ' . $row["status"] . '">' . $row["status"] . '</span></td>
                            <td><a href="./idea_details.php?agent_profile_id=' . $agentAccountId . '&business_idea_id=' . $ideaId . '">View</a></td>
                        </tr>';
                                }
                            }
                        }

                        ?>
                    </tbody>
                </table>
            </div>

        </section>
    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>