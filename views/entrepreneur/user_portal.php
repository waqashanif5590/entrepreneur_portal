<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/user_portal.css">
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
            <div class="search_bar">
                <form action="../search/search.php" method="get">
                    <input type="text" name="q" id="search" placeholder="Search mentors, ideas, templates, resources...">
                    <button id="search_button"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <h1>Welcome to the User Portal</h1>
            <p>Empowering your entrepreneurial journey — from idea to execution. Find mentors, resources, templates
                and funding all in one place.</p>

            <div class="cards_container">
                <div class="card">
                    <h2>Mentors</h2>
                    <p>Connect with experienced entrepreneurs and industry experts to guide your business journey.</p>
                    <a href="../profiles/agent_profiles.php">Explore <img src="../../public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                </div>
                <div class="card">
                    <h2>Resources</h2>
                    <p>Access a wealth of resources including articles, guides, and tools to help you grow your
                        business.</p>
                    <a href="./templates_list.php">Explore <img src="../../public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                </div>
                <div class="card">
                    <h2>Templates</h2>
                    <p>Utilize business templates for plans, financials, and marketing to streamline your operations.
                    </p>
                    <a href="./templates_list.php">Explore <img src="../../public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                </div>
                <div class="card">
                    <h2>Funding</h2>
                    <p>Explore funding options and connect with investors to secure the capital you need.</p>
                    <a href="./templates_list.php">Explore <img src="../../public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                </div>
            </div>

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
                                $business_idea_id = (int)$row["id"];
                                $agent_profile_id = (int)$row["user_id"];
                                echo '<div class="card business_idea_card">
                                        <h2>' . $row["idea_title"] . '</h2>
                                        <p>' . substr($row["problem_statement"], 0, 170) . ' ........ </p>
                                        <div class="idea_bottom_section">
                                        <a href="../ideas/idea_details.php?business_idea_id=' . $business_idea_id . '&agent_profile_id=' . $agent_profile_id . '">Learn More <img src="../../public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
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

            <div class="mentors_recommendations">
                <h1>Mentor Recommendations</h1>
                <div class="mentors_card_container">

                    <?php
                    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
                        $sql = "SELECT * FROM `profiles` WHERE `status`='Approved'";
                        $result = mysqli_query($conn, $sql);
                        $numRows = mysqli_num_rows($result);
                        // if ($numRows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $agent_profile_id = (int)$row["agent_account_id"];
                            // $_SESSION["id"] = $row["id"];
                            echo ' <div class="mentors_card">
                                            <div class="mentor">
                                                <div class="mentor_icon">
                                                    <img src="../../uploads/profiles/' . $row['profile_image'] . '" alt="Profile Image">
                                                </div>
                                                <div class="mentor_profile">
                                                    <h1>' . $row["agent_f_name"] . ' ' . $row["agent_l_name"] . '</h1>
                                                    <p>' . $row["field_expertise"] . '</p>
                                                </div>
                                            </div>
                                           <a href="../profiles/selected_agent_ideas.php?agent_profile_id=' . $agent_profile_id . '">Explore</a>
                                        </div>';
                        }
                        if ($numRows == 0) {
                            echo "No mentors found";
                        }
                    }
                    // }
                    ?>

                </div>
            </div>
        </section>

    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>