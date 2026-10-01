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

                <?php
                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                    if (isset($_GET['author_id'])) {
                        $author_id = base64_decode($_GET['author_id']);
                        if (!is_numeric($author_id)) {
                            die('Invalid request');
                        }

                        $sql = "SELECT * FROM `profiles` WHERE agent_account_id = '$author_id'";
                        $result = mysqli_query($conn, $sql);
                        $numRows = mysqli_num_rows($result);
                        if ($numRows == 0) {
                            $url = "create_profile.php";
                        }
                        if ($numRows > 0) {
                            while ($row = mysqli_fetch_array($result)) {
                                if ($row['status'] === 'Pending') {
                                    $alert = "Your profile is pending for admin approval. Please wait for admin response";
                                    $url = "agent_portal.php?alert=$alert";
                                } else {

                                    echo '<div class="profile_card">
                            <div class="profile_image">
                            <img src="../uploads/profiles/' . $row['profile_image'] . '" alt="Profile Image">
                            </div>
                            <div class="profile_info">
                            <h2>' . $row["agent_f_name"] . ' ' . $row["agent_l_name"] . '</h2>
                            <p><strong>Email: </strong> <a href="#">' . $row["agent_email"] . '</a></p>
                            <p><strong>Phone: </strong>' . $row["contact"] . '</p>
                            <p><strong>Location: </strong>' . $row["city"] . '</p>
                            <p><strong>Bio: </strong>' . $row["agent_bio"] . '</p>
                            </div>
                            </div>';
                                }
                            }
                        }
                    }
                };
                ?>
                <!-- Engagement Section -->
                <?php
                // Check if engagement_stats row exists for this agent
                $author_id = isset($_GET['author_id']) ? base64_decode($_GET['author_id']) : null;
                $check_sql = "SELECT * FROM engagement_stats WHERE agent_profile_id='$author_id'";
                $check_result = mysqli_query($conn, $check_sql);

                if (mysqli_num_rows($check_result) == 0) {
                    // Create engagement_stats row if it doesn't exist
                    $insert_sql = "INSERT INTO engagement_stats (agent_profile_id, likes, followers, rating, total_ratings)
                                     VALUES ('$author_id', 0, 0, 0, 0)";
                    mysqli_query($conn, $insert_sql);
                }

                // Now get the stats
                $sql = "SELECT * FROM engagement_stats WHERE agent_profile_id='$author_id'";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                echo '
                <div class="engagement_stats">

                    <div class="stat_box">
                        ⭐ <span>' . number_format($row["rating"], 1) . '</span>
                        <p>Rating</p>
                    </div>
                    <div class="stat_box">
                        👍 <span>' . $row["likes"] . '</span>
                        <p>Likes</p>
                    </div>
                    <div class="stat_box">
                        👥 <span>' . $row["followers"] . '</span>
                        <p>Followers</p>
                    </div>
                </div>';
                ?>

                <!-- Buttons -->
                <div class="profile_actions">
                    <?php
                    $user_id = $_SESSION['id'];

                    // Check if user has already liked
                    $like_check = mysqli_query($conn, "SELECT id FROM user_likes WHERE user_id='$user_id' AND agent_profile_id='$author_id'");
                    $has_liked = mysqli_num_rows($like_check) > 0;

                    // Check if user is already following
                    $follow_check = mysqli_query($conn, "SELECT id FROM user_follows WHERE user_id='$user_id' AND agent_profile_id='$author_id'");
                    $is_following = mysqli_num_rows($follow_check) > 0;

                    // Check if user has already rated
                    $rate_check = mysqli_query($conn, "SELECT rating FROM user_ratings WHERE user_id='$user_id' AND agent_profile_id='$author_id'");
                    $has_rated = mysqli_num_rows($rate_check) > 0;
                    $user_rating = $has_rated ? mysqli_fetch_assoc($rate_check)['rating'] : 0;
                    ?>
                    <?php if ($has_liked): ?>
                        <button class="like_btn liked" disabled>You Liked</button>
                    <?php else: ?>
                        <a href="like.php?author_id=<?php echo $author_id; ?>">
                            <button class="like_btn">Like</button>
                        </a>
                    <?php endif; ?>

                    <?php if ($is_following): ?>
                        <button class="follow_btn following" disabled>Following</button>
                    <?php else: ?>
                        <a href="follow.php?author_id=<?php echo $author_id; ?>">
                            <button class="follow_btn">Follow</button>
                        </a>
                    <?php endif; ?>
                </div>
            </div>


            <h1 class="list_heading">List of all forums</h1>
            <div class="forums_list">
                <?php
                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                    $sql = "SELECT * FROM `forums` WHERE author_id = '$author_id'";
                    $result = mysqli_query($conn, $sql);
                    $numRows = mysqli_num_rows($result);
                    if ($numRows == 0) {
                        echo '<div class="empty_card">
                        <h2 class="problem_title">No forum exists</h2>
                        <p class="problem_statement">You have not created any forum.</p>
                        <a href="' . $url . '" class="explore_btn">Create Now</a>
                    </div>';
                    } else {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $encoded_author_id = base64_encode($row["author_id"]);
                            $encoded_forum_id = base64_encode($row["forum_id"]);
                            echo ' <div class="card">
                                <div class="card_top">
                        <h2 class="problem_title">' . $row["forum_title"] . '</h2>
                         <span class="status badge_' . $row["status"] . '">' . $row["status"] . '</span>
                        </div>
                        <p class="problem_statement">' . $row["forum_desc"] . '</p>
                        <div class="card_bottom">
                            <a href="./forum.php?author_id=' . $encoded_author_id . '&forum_id=' . $encoded_forum_id . '" class="explore_btn">Explore</a>
                            <span class="date">' . $row["date"] . '</span>
                        </div>
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