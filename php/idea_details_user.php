<?php
session_start();
include '../database.php';
if (isset($_GET['business_idea_id']) && isset($_GET['agent_profile_id'])) {
    $business_idea_id = base64_decode($_GET['business_idea_id']);
    $agent_profile_id = base64_decode($_GET['agent_profile_id']);
    if (!is_numeric($business_idea_id) || !is_numeric($agent_profile_id)) {
        die('Invalid request');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idea Details</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/idea_details.css">
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
                    $sql = "SELECT * FROM `profiles` WHERE agent_account_id = '$agent_profile_id'";
                    $result = mysqli_query($conn, $sql);
                    $numRows = mysqli_num_rows($result);
                    if ($numRows > 0) {
                        while ($row = mysqli_fetch_array($result)) {
                            echo '<div class="profile_card">
                            <div class="profile_image">
                            <img src="../uploads/profiles/' . $row['profile_image'] . '" alt="">
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
                <?php
                if (isset($_GET['alert'])) {
                    $alert = $_GET['alert'];
                    echo '<p id="alert_message">' . $alert . '</p>';
                    unset($alert);
                }
                ?>

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
                        <a href="like.php?agent_profile_id=<?php echo $agent_profile_id; ?>">
                            <button class="like_btn">Like</button>
                        </a>
                    <?php endif; ?>

                    <?php if ($is_following): ?>
                        <button class="follow_btn following" disabled>Following</button>
                    <?php else: ?>
                        <a href="follow.php?agent_profile_id=<?php echo $agent_profile_id; ?>">
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

            <div class="business_details">
                <h1>Here is My business details:</h1>
                <?php
                // $business_idea_id = $_SESSION["business_idea_id"];
                $sql = "SELECT * FROM `business_ideas` WHERE idea_id='$business_idea_id'";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {

                    echo '
                        <div class="field_set">
                        <h2>Business Title</h2>
                        <p>' . $row["idea_title"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Business Category</h2>
                        <p>' . $row["idea_category"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Business Stage</h2>
                        <p>' . $row["idea_stage"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Problem Statement</h2>
                        <p>' . $row["problem_statement"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Proposed Solutions</h2>
                        <p>' . $row["problem_solution"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Unique Value Proposition</h2>
                        <p>' . $row["proposition_value"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Target Market</h2>
                        <p>' . $row["target_market"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Market Size</h2>
                        <p>' . $row["market_size"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Business Model</h2>
                        <!-- Explain how the idea will generate revenue -->
                        <p>' . $row["business_model"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Required Resources</h2>
                        <p>' . $row["resources"] . '</p>
                        </div>
                        <div class="field_set">
                        <h2>Expected Outcome</h2>
                        <p>' . $row["outcomes"] . '</p>
                        </div>
                        </div>';
                }
                ?>
                <div class="rating">
                    <h1>Rate this Business Idea:</h1>
                    <div class="stars">
                        <span class="star" data-value="1">&#9733;</span>
                        <span class="star" data-value="2">&#9733;</span>
                        <span class="star" data-value="3">&#9733;</span>
                        <span class="star" data-value="4">&#9733;</span>
                        <span class="star" data-value="5">&#9733;</span>
                    </div>
                </div>

                <div class="comment_section">
                    <h1>Reviews</h1>
                    <div class="comments">
                        <?php
                        $sql = "SELECT * FROM `reviews` WHERE business_idea_id='$business_idea_id' ORDER BY `submission_date` DESC";
                        $result = mysqli_query($conn, $sql);
                        $numRows = mysqli_num_rows($result);
                        if ($numRows == 0) {
                            echo '<p>No reviews yet. Be the first to review this business idea!</p>';
                        }
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<div class="comment">
                                <div class="image">
                                    <img src="../images/profile.png" alt="">
                                </div>
                                <div class="comment_content">
                                    <h1>' . $row["sender_name"] . '</h1>
                                    <p>' . $row["comment_content"] . '</p>
                                </div>
                            </div>';
                        }
                        ?>
                        <!-- <div class="comment">
                                <div class="image">
                                    <img src="./images/profile.png" alt="">
                                </div>
                                <div class="comment_content">
                                    <h1>John Wicely</h1>
                                    <p>Great business idea! Focusing on telemedicine is very timely given the current global
                                        health trends.</p>
                                </div>
                            </div> -->
                    </div>
                    <div class="add_comment">
                        <?php
                        $sql = "SELECT * FROM `business_ideas` WHERE idea_id='$business_idea_id'";
                        $result = mysqli_query($conn, $sql);
                        $row = mysqli_fetch_assoc($result);
                        $business_idea_id = base64_encode($row["idea_id"]);
                        $agent_profile_id = base64_encode($row["agent_profile_id"]);
                        ?>
                        <form action="./submit_review.php?business_idea_id=<?php echo $business_idea_id; ?>&agent_profile_id=<?php echo $agent_profile_id; ?>" method="post">
                            <textarea name="review" placeholder="Add your comment here..." style="width: 100%;"></textarea>
                            <button type="submit">Submit</button>
                        </form>
                    </div>
                </div>
            </div>

        </section>


    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../JS/siderbar.js"></script>

    <script>
        document.querySelectorAll(".star").forEach(star => {
            star.addEventListener("click", function() {
                let rating = this.getAttribute("data-value");
                let id = "<?php echo $agent_profile_id; ?>";

                window.location.href = "rate.php?id=" + id + "&rating=" + rating;
            });
        });
    </script>
</body>

</html>