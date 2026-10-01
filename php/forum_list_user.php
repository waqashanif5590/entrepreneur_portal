<?php
session_start();
include '../database.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum List</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/forum_list.css">
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
            <div class="profile_section">
                <div class="forum_header">
                    <h1 class="welcome_text">Welcome to forums list</h1>
                    <p class="roles">Community Guidelines:</p>
                    <ul>
                        <li>Treat everyone with kindness and respect, even if you disagree.</li>
                        <li>Keep your comments on-topic and relevant to the discussion.</li>
                        <li>Avoid personal attacks, insults or harassment.</li>
                        <li>Share your thoughts and opinion in a respectful and helpful way.</li>
                        <li>Keep self-promotion and irrelevant links to a minimum.</li>
                    </ul>
                </div>
                <h1>List of all forums</h1>
                <div class="forums_list">
                    <?php
                    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                        $sql = "SELECT * FROM `forums` WHERE `status`='Approved'";
                        $result = mysqli_query($conn, $sql);
                        $numRows = mysqli_num_rows($result);
                        if ($numRows == 0) {
                            echo '<div class="card">
                        <h2 class="problem_title">No forum exists</h2>
                        <p class="problem_statement">No one have started a forum.</p>
                        <a href="./user_portal.php" class="explore_btn">Back to home</a>
                    </div>';
                        } else {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $author_id = base64_encode($row["author_id"]);
                                $forum_id = base64_encode($row["forum_id"]);
                                echo ' <div class="card">
                        <h2 class="problem_title">' . $row["forum_title"] . '</h2>
                        <p class="problem_statement">' . $row["forum_desc"] . '</p>
                        <div class="card_bottom">
                            <a href="./forum.php?author_id=' . $author_id . '&forum_id=' . $forum_id . '" class="explore_btn">Explore</a>
                            <span class="date">' . $row["date"] . '</span>
                        </div>
                    </div>';
                            }
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