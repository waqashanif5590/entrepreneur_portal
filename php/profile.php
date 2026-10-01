<?php
session_start();
include '../database.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/profile.css">
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
            <!-- profile details -->
            <div class="profile_section">
                <?php
                if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                    $sql = "SELECT * FROM `profiles` WHERE `agent_account_id` = 5";
                    $result = mysqli_query($conn, $sql);
                    $numRows = mysqli_num_rows($result);
                    if ($numRows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {

                            echo '
                <div class="profile_header">
                    <div class="profile_image">
                        <img src="../images/profile.png" alt="">
                    </div>
                    <div class="profile_header_content">
                        <h1>' . $row["agent_f_name"] . ' ' . $row["agent_l_name"] . '</h1>
                        <span class="email">' . $row["agent_email"] . '</span>
                        <p class="expertise">' . $row["field_expertise"] . '</p>
                        <div class="profile_stat">
                            <p><strong>Followers:</strong> 0</p>
                            <p><strong>Likes:</strong> 0</p>
                            <p><strong>Views:</strong> 0</p>
                        </div>
                        <div class="profile_btn_group">
                            <button id="update_profile_btn">Like</button>
                            <button id="delete_profile_btn">Follow</button>
                        </div>
                    </div>

                </div>

                <div class="profile_info">
                    <div class="field">
                        <h1>Name:</h1>
                        <p>' . $row["agent_f_name"] . ' ' . $row["agent_l_name"] . '</p>
                    </div>
                    <div class="field">
                        <h1>Date of Birth</h1>
                        <p>' . $row["dob"] . '</p>
                    </div>
                    <div class="field">
                        <h1>Age:</h1>
                        <p>' . $row["dob"] . '</p>
                    </div>
                    <div class="field">
                        <h1>Country:</h1>
                        <p>' . $row["country"] . '</p>
                    </div>
                    <div class="field">
                        <h1>City:</h1>
                        <p>' . $row["city"] . '</p>
                    </div>
                    <div class="field">
                        <h1>Contact:</h1>
                        <p>' . $row["contact"] . '</p>
                    </div>
                    <div class="field">
                        <h1>Email:</h1>
                        <p>' . $row["agent_email"] . '</p>
                    </div>
                    <div class="field">
                        <h1>Field of interest:</h1>
                        <p>' . $row["field_expertise"] . '</p>
                    </div>
                    <div class="field">
                        <h1>Organization:</h1>
                        <p>' . $row["org_name"] . '</p>
                    </div>
                    <div class="field">
                        <h1>Experience in related field:</h1>
                        <p>' . $row["experience"] . '</p>
                    </div>
                    <div class="field">
                        <h1>Personal Website:</h1>
                        <p><a href="' . $row["agent_weblink"] . '">' . $row["agent_weblink"] . '</a></p>
                    </div>
                    <div class="field summary">
                        <h1>Summary:</h1>
                        <p>' . $row["agent_bio"] . '</p>
                    </div>
                </div>';
                        }
                    } else {
                        echo "No profiles found";
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