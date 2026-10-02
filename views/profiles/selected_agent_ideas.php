<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
$viewerRole = requireAccountRole($conn, ['admin', 'agent', 'user']);
$csrfToken = csrfToken();
$agent_profile_id = requestPositiveId($_GET['agent_profile_id'] ?? null);
if ($agent_profile_id === null) {
    http_response_code(400);
    exit('Invalid agent request.');
}
$viewerId = (int)$_SESSION['id'];
$profileStatement = $conn->prepare('SELECT * FROM profiles WHERE agent_account_id = ? LIMIT 1');
$profileStatement->bind_param('i', $agent_profile_id);
$profileStatement->execute();
$agentProfile = $profileStatement->get_result()->fetch_assoc();
$profileStatement->close();
$agentAccountStatement = $conn->prepare("SELECT 1 FROM accounts WHERE id = ? AND entity_type = 'agent' AND status = 'Active' LIMIT 1");
$agentAccountStatement->bind_param('i', $agent_profile_id);
$agentAccountStatement->execute();
$agentAccountIsActive = $agentAccountStatement->get_result()->num_rows > 0;
$agentAccountStatement->close();
if (!$agentProfile || !$agentAccountIsActive || ($agentProfile['status'] !== 'Approved' && $viewerRole !== 'admin' && $viewerId !== $agent_profile_id)) {
    http_response_code(404);
    exit('Agent not found.');
}
$viewerCanEngage = $viewerRole !== 'admin' && $viewerId !== $agent_profile_id;
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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
                echo '<p id="alert_message">' . $escape($alert) . '</p>';
                unset($alert);
            }
            ?>
            <div class="profile_section">

                <?php
                if ($agentProfile) {
                        $row = $agentProfile;
                            echo '<div class="profile_card">
                            <div class="profile_image">
                            <img src="profile_image.php?agent_account_id=' . (int)$agent_profile_id . '" alt="Agent Profile">
                            </div>
                            <div class="profile_info">
                            <h2>' . $escape($row["agent_f_name"]) . ' ' . $escape($row["agent_l_name"]) . '</h2>
                            <p><strong>Email: </strong> <a href="#">' . $escape($row["agent_email"]) . '</a></p>
                            <p><strong>Phone: </strong>' . $escape($row["contact"]) . '</p>
                            <p><strong>Location: </strong>' . $escape($row["city"]) . '</p>
                            <p><strong>Bio: </strong>' . $escape($row["agent_bio"]) . '</p></div></div>';
                }
                ?>
                <!-- Engagement Section -->
                <div class="engagement_stats">
                    <?php
                    // Check if engagement_stats row exists for this agent
                    $checkStatement = $conn->prepare('SELECT 1 FROM engagement_stats WHERE agent_profile_id = ? LIMIT 1');
                    $checkStatement->bind_param('i', $agent_profile_id);
                    $checkStatement->execute();
                    $statsExist = $checkStatement->get_result()->num_rows > 0;
                    $checkStatement->close();
                    if (!$statsExist) {
                        $insertStatement = $conn->prepare('INSERT INTO engagement_stats (agent_profile_id, likes, followers, rating, total_ratings) VALUES (?, 0, 0, 0, 0)');
                        $insertStatement->bind_param('i', $agent_profile_id);
                        $insertStatement->execute();
                        $insertStatement->close();
                    }

                    $statsStatement = $conn->prepare('SELECT * FROM engagement_stats WHERE agent_profile_id = ? LIMIT 1');
                    $statsStatement->bind_param('i', $agent_profile_id);
                    $statsStatement->execute();
                    $row = $statsStatement->get_result()->fetch_assoc();
                    $statsStatement->close();

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
                    $user_id = $viewerId;
                    $likeStatement = $conn->prepare('SELECT id FROM user_likes WHERE user_id = ? AND agent_profile_id = ? LIMIT 1');
                    $likeStatement->bind_param('ii', $user_id, $agent_profile_id);
                    $likeStatement->execute();
                    $has_liked = $likeStatement->get_result()->num_rows > 0;
                    $likeStatement->close();
                    $followStatement = $conn->prepare('SELECT id FROM user_follows WHERE user_id = ? AND agent_profile_id = ? LIMIT 1');
                    $followStatement->bind_param('ii', $user_id, $agent_profile_id);
                    $followStatement->execute();
                    $is_following = $followStatement->get_result()->num_rows > 0;
                    $followStatement->close();
                    $rateStatement = $conn->prepare('SELECT rating FROM user_ratings WHERE user_id = ? AND agent_profile_id = ? LIMIT 1');
                    $rateStatement->bind_param('ii', $user_id, $agent_profile_id);
                    $rateStatement->execute();
                    $ratedRow = $rateStatement->get_result()->fetch_assoc();
                    $rateStatement->close();
                    $has_rated = (bool)$ratedRow;
                    $user_rating = $ratedRow['rating'] ?? 0;
                    ?>

                    <?php if (!$viewerCanEngage): ?>
                    <?php elseif ($has_liked): ?>
                        <button class="like_btn liked" disabled>You Liked</button>
                    <?php else: ?>
                        <form action="../actions/like.php" method="post"><input type="hidden" name="csrf_token" value="<?php echo $escape($csrfToken); ?>"><input type="hidden" name="agent_profile_id" value="<?php echo $agent_profile_id; ?>"><button class="like_btn" type="submit">Like</button></form>
                    <?php endif; ?>

                    <?php if (!$viewerCanEngage): ?>
                    <?php elseif ($is_following): ?>
                        <button class="follow_btn following" disabled>Following</button>
                    <?php else: ?>
                        <form action="../actions/follow.php" method="post"><input type="hidden" name="csrf_token" value="<?php echo $escape($csrfToken); ?>"><input type="hidden" name="agent_profile_id" value="<?php echo $agent_profile_id; ?>"><button class="follow_btn" type="submit">Follow</button></form>
                    <?php endif; ?>
                </div>

                <!-- Rating Section -->
                <?php if ($viewerCanEngage): ?>
                    <div class="rating_section">
                        <h3>Rate this Agent</h3>
                        <?php if ($has_rated): ?>
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $user_rating ? 'selected' : ''; ?>" data-value="<?php echo $i; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <p class="rating_message">You rated this agent <?php echo (int)$user_rating; ?> stars</p>
                        <?php else: ?>
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?php echo $i; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <p class="rating_message">Click on stars to rate</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card_container">
                <?php
                if ($viewerRole === 'agent') {
                $agent_id = $viewerId;
                $ownProfileStatement = $conn->prepare('SELECT status FROM profiles WHERE agent_account_id = ? LIMIT 1');
                $ownProfileStatement->bind_param('i', $agent_id);
                $ownProfileStatement->execute();
                $ownProfile = $ownProfileStatement->get_result()->fetch_assoc();
                $ownProfileStatement->close();

                        echo '  <div class="add_card">
                        <h2>Add New Business Domain</h2>';


                        if (!$ownProfile) {
                            $url = '../mentor/create_profile.php';
                            echo '<a href="' . $url . '" class="explore_btn"><i class="fas fa-plus"></i></a>';
                        } else {
                            if ($ownProfile["status"] == "Pending") {
                                echo "Your profile is pending for admin approval.";
                            } else if ($ownProfile["status"] == "Blocked") {
                                echo "Your profile is Blocked. Please wait for admin approval.";
                            } else {
                                $url = '../mentor/add_business_domain.php';
                                echo '<a href="' . $url . '" class="explore_btn"><i class="fas fa-plus"></i></a>';
                            }
                        }
                    }
                ?>
            </div>


            <h1>List of all my approved ideas</h1>
            <div class="ideas_list">
                <?php
                if ($viewerRole === 'admin' || $viewerId === $agent_profile_id) {
                    $ideasStatement = $conn->prepare("SELECT * FROM business_ideas WHERE user_id = ? AND status = 'Approved' ORDER BY id DESC");
                    $ideasStatement->bind_param('i', $agent_profile_id);
                } elseif ($viewerRole === 'agent') {
                    $ideasStatement = $conn->prepare("SELECT * FROM business_ideas WHERE user_id = ? AND status = 'Approved' AND visibility IN ('Public', 'Mentors Only') ORDER BY id DESC");
                    $ideasStatement->bind_param('i', $agent_profile_id);
                } else {
                    $ideasStatement = $conn->prepare("SELECT * FROM business_ideas WHERE user_id = ? AND status = 'Approved' AND visibility = 'Public' ORDER BY id DESC");
                    $ideasStatement->bind_param('i', $agent_profile_id);
                }
                $ideasStatement->execute();
                $result = $ideasStatement->get_result();
                if ($result->num_rows === 0) {
                    echo '<div class="card">
                        <h2>No business template exist.</h2>
                        <p>No approved idea exists.</p>
                    </div>';
                }
                while ($row = $result->fetch_assoc()) {
                    $entity_type = $viewerRole;
                    if ($entity_type == 'user') {
                        $link_to_page = '../ideas/idea_details.php?business_idea_id=' . (int)$row['id'] . '&agent_profile_id=' . (int)$row['user_id'];
                    } else if ($entity_type == 'agent') {
                        $link_to_page = '../ideas/idea_details.php?business_idea_id=' . (int)$row['id'] . '&agent_profile_id=' . (int)$row['user_id'];
                    } else {
                        $link_to_page = '../ideas/idea_details.php?business_idea_id=' . (int)$row['id'] . '&agent_profile_id=' . (int)$row['user_id'];
                    }
                    echo ' <div class="card">
                        <h2>' . $escape($row["idea_title"]) . '</h2>
                        <p>' . $escape($row["problem_statement"]) . '</p>
                        <div class="card_bottom">
                            <a href="' . $link_to_page . '" class="explore_btn">Explore</a>
                            <span class="category">' . $escape($row["idea_category"]) . '</span>
                        </div>
                    </div>';
                };
                $ideasStatement->close();
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
                const message = document.querySelector('.rating_message');
                const hasRated = message && message.textContent.includes('You rated this agent');

                if (hasRated) {
                    alert('You have already rated this agent');
                    return;
                }

                const form = document.createElement("form");
                form.method = "post";
                form.action = "../actions/rate.php";
                [["csrf_token", "<?php echo $escape($csrfToken); ?>"], ["agent_profile_id", "<?php echo $agent_profile_id; ?>"], ["rating", this.getAttribute("data-value")]].forEach(([name, value]) => {
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            });
        });
    </script>
</body>

</html>