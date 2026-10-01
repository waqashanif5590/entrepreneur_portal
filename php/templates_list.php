<?php
session_start();
include '../database.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/user_portal.css">
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
            <div class="search_bar">
                <form action="search.php" method="get">
                    <input type="text" name="q" id="search" placeholder="Search mentors, ideas, templates, resources...">
                    <button id="search_button"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <h1>Welcome to the User Portal</h1>
            <p>Empowering your entrepreneurial journey — from idea to execution. Find mentors, resources, templates
                and funding all in one place.</p>

            <div class="business_ideas">
                <h1>Featured business ideas</h1>
                <div class="business_card_container">
                    <?php
                    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
                        $sql = "SELECT * FROM `business_ideas` WHERE `status`='Approved'";
                        $result = mysqli_query($conn, $sql);
                        $numRows = mysqli_num_rows($result);
                        if ($numRows > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                // $_SESSION['agent_profile_id'] = $row['agent_profile_id'];
                                $business_idea_id = base64_encode($row["idea_id"]);
                                $agent_profile_id = base64_encode($row["agent_profile_id"]);
                                echo '<div class="business_idea_card">
                                        <h2>' . $row["idea_title"] . '</h2>
                                        <p>' . substr($row["problem_statement"], 0, 170) . ' ........ </p>
                                        <div class="idea_bottom_section">
                                        <a href="./idea_details_user.php?business_idea_id=' . $business_idea_id . '&agent_profile_id=' . $agent_profile_id . '">Learn More <img src="./arrow_icon.png" alt="" class="arrow_icon"></a>
                                        <span>' . $row["idea_category"] . '</span>
                                        </div>
                                    </div>';
                            }
                        } else {
                            echo "No ideas found";
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

    <script src="../JS/siderbar.js"></script>
</body>

</html>