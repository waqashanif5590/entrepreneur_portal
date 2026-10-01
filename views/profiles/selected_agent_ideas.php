<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
if (isset($_GET['agent_profile_id'])) {
    $agent_profile_id = base64_decode($_GET['agent_profile_id']);
    if (!is_numeric($agent_profile_id)) {
        die('Invalid request');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idea list</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/selected_agent_ideas.css">
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
            <div class="profile_section">

                <?php
                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                    $sql = "SELECT * FROM `profiles` WHERE agent_account_id = '$agent_profile_id'";
                    $result = mysqli_query($conn, $sql);
                    $numRows = mysqli_num_rows($result);
                    if ($numRows > 0) {
                        while ($row = mysqli_fetch_array($result)) {
                            echo '<div class="profile_card">
                            <div class="profile_image">
                            <img src="../../uploads/profiles/' . $row["profile_image"] . '" alt="Agent Profile">
                            </div>
                            <div class="profile_info">
                            <h2>' . $row["agent_f_name"] . ' ' . $row["agent_l_name"] . '</h2>
                            <p><strong>Email: </strong> <a href="#">' . $row["agent_email"] . '</a></p>
                            <p><strong>Phone: </strong>' . $row["contact"] . '</p>
                            <p><strong>Location: </strong>' . $row["city"] . '</p>
                            <p><strong>Bio: </strong>' . $row["agent_bio"] . '</p></div></div>';
                        }
                    }
                };
                ?>
                <!-- Engagement Section -->
                <div class="engagement_stats">
                    <?php
                    // Check if engagement_stats row exists for this agent
                    $check_sql = "SELECT * FROM engagement_stats WHERE agent_profile_id='$agent_profile_id'";
                    $check_result = mysqli_query($conn, $check_sql);

                    if (mysqli_num_rows($check_result) == 0) {
                        // Create engagement_stats row if it doesn't exist
                        $insert_sql = "INSERT INTO engagement_stats (agent_profile_id, likes, followers, rating, total_ratings)
                                     VALUES ('$agent_profile_id', 0, 0, 0, 0)";
                        mysqli_query($conn, $insert_sql);
                    }

                    // Now get the stats
                    $sql = "SELECT * FROM engagement_stats WHERE agent_profile_id='$agent_profile_id'";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);

                    echo '   <div class="stat_box">
                        ⭐ <span>' . number_format($row["rating"], 1) . '</span>
                        <p>Rating</p>
                    </div>
                    <div class="stat_box">
                        👍 <span>' . $row['likes'] . '</span>
                        <p>Likes</p>
                    </div>
                    <div class="stat_box">
                        👥 <span>' . $row["followers"] . '</span>
                        <p>Followers</p>
                    </div>';
                    ?>

                </div>

                <!-- Buttons -->
                <div class="profile_actions">
                    <?php
                    $user_id = $_SESSION['id'];

                    // Check if user has already liked
                    $like_check = mysqli_query($conn, "SELECT id FROM user_likes WHERE user_id='$user_id' AND agent_profile_id='$agent_profile_id'");
                    $has_liked = mysqli_num_rows($like_check) > 0;

                    // Check if user is already following
                    $follow_check = mysqli_query($conn, "SELECT id FROM user_follows WHERE user_id='$user_id' AND agent_profile_id='$agent_profile_id'");
                    $is_following = mysqli_num_rows($follow_check) > 0;

                    // Check if user has already rated
                    $rate_check = mysqli_query($conn, "SELECT rating FROM user_ratings WHERE user_id='$user_id' AND agent_profile_id='$agent_profile_id'");
                    $has_rated = mysqli_num_rows($rate_check) > 0;
                    $user_rating = $has_rated ? mysqli_fetch_assoc($rate_check)['rating'] : 0;
                    ?>

                    <?php if ($has_liked): ?>
                        <button class="like_btn liked" disabled>You Liked</button>
                    <?php else: ?>
                        <a href="../actions/like.php?agent_profile_id=<?php echo $agent_profile_id; ?>">
                            <button class="like_btn">Like</button>
                        </a>
                    <?php endif; ?>

                    <?php if ($is_following): ?>
                        <button class="follow_btn following" disabled>Following</button>
                    <?php else: ?>
                        <a href="../actions/follow.php?agent_profile_id=<?php echo $agent_profile_id; ?>">
                            <button class="follow_btn">Follow</button>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Rating Section -->
                <div class="rating_section">
                    <h3>Rate this Agent</h3>
                    <?php if ($has_rated): ?>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $user_rating ? 'selected' : ''; ?>" data-value="<?php echo $i; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <p class="rating_message">You rated this agent <?php echo $user_rating; ?> stars</p>
                    <?php else: ?>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star" data-value="<?php echo $i; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <p class="rating_message">Click on stars to rate</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card_container">
                <?php
                if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
                    $agent_id = $_SESSION["id"];


                    $sql2 = "SELECT * FROM `accounts` WHERE id='$agent_id'";
                    $result2 = mysqli_query($conn, $sql2);
                    $row2 = mysqli_fetch_assoc($result2);
                    if ($row2['entity_type'] === 'agent') {

                        $sql = "SELECT * FROM `profiles` WHERE `agent_account_id` = '$agent_id'";
                        $result = mysqli_query($conn, $sql);
                        $numRows = mysqli_num_rows($result);
                        $row = mysqli_fetch_assoc($result);

                        echo '  <div class="add_card">
                        <h2>Add New Business Domain</h2>';


                        if ($numRows == 0) {
                            $url = '../mentor/create_profile.php';
                            echo '<a href="' . $url . '" class="explore_btn"><i class="fas fa-plus"></i></a>';
                        } else {
                            if ($row["status"] == "Pending") {
                                echo "Your profile is pending for admin approval.";
                            } else if ($row["status"] == "Blocked") {
                                echo "Your profile is Blocked. Please wait for admin approval.";
                            } else {
                                $url = '../mentor/add_business_domain.php';
                                echo '<a href="' . $url . '" class="explore_btn"><i class="fas fa-plus"></i></a>';
                            }
                        }
                    }
                }
                ?>
            </div>


            <h1>List of all my approved ideas</h1>
            <div class="ideas_list">
                <?php
                $sql = "SELECT * FROM `business_ideas` WHERE `user_id`='$agent_profile_id' AND `status`='Approved'";
                $result = mysqli_query($conn, $sql);
                $numRows = mysqli_num_rows($result);
                if ($numRows == 0) {
                    echo '<div class="card">
                        <h2>No business template exist.</h2>
                        <p>No approved idea exists.</p>
                    </div>';
                }
                while ($row = mysqli_fetch_assoc($result)) {
                    $business_idea_id = base64_encode($row["id"]);
                    $agent_profile_id = base64_encode($row["user_id"]);
                    $sql = "SELECT entity_type FROM `accounts` WHERE id='$user_id'";
                    $result_entity = mysqli_query($conn, $sql);
                    $entity_type = mysqli_fetch_assoc($result_entity)['entity_type'];
                    if ($entity_type == 'user') {
                        $link_to_page = "../ideas/idea_details.php?business_idea_id=' . $business_idea_id . '&agent_profile_id=' . $agent_profile_id . '";
                    } else if ($entity_type == 'agent') {
                        $link_to_page = "../ideas/idea_details.php?business_idea_id=' . $business_idea_id . '&agent_profile_id=' . $agent_profile_id . '";
                    } else {
                        $link_to_page = "../ideas/idea_details.php?business_idea_id=' . $business_idea_id . '&agent_profile_id=' . $agent_profile_id . '";
                    }
                    echo ' <div class="card">
                        <h2>' . $row["idea_title"] . '</h2>
                        <p>' . $row["problem_statement"] . '</p>
                        <div class="card_bottom">
                            <a href="' . $link_to_page . '" class="explore_btn">Explore</a>
                            <span class="category">' . $row["idea_category"] . '</span>
                        </div>
                    </div>';
                };
                ?>
            </div>

        </section>
    </div>

    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>

    <script>
        document.querySelectorAll(".star").forEach(star => {
            star.addEventListener("click", function() {
                // Check if user has already rated (by checking if stars are already selected)
                const hasRated = document.querySelector('.rating_message').textContent.includes('You rated this agent');

                if (hasRated) {
                    alert('You have already rated this agent');
                    return;
                }

                let rating = this.getAttribute("data-value");
                let id = "<?php echo $agent_profile_id; ?>";

                window.location.href = "../actions/rate.php?id=" + id + "&rating=" + rating;
            });
        });
    </script>
</body>

</html>