<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (empty($_SESSION['loggedin']) || empty($_SESSION['id'])) {
    http_response_code(403);
    exit('Please sign in to view this idea.');
}

$accountId = (int)$_SESSION['id'];
$roleStatement = $conn->prepare('SELECT entity_type FROM accounts WHERE id = ?');
$roleStatement->bind_param('i', $accountId);
$roleStatement->execute();
$account = $roleStatement->get_result()->fetch_assoc();
$roleStatement->close();
$role = $account['entity_type'] ?? '';
if (!in_array($role, ['admin', 'agent', 'user'], true)) {
    http_response_code(403);
    exit('Access denied.');
}

$encodedIdeaId = $_GET['business_idea_id'] ?? '';
$ideaId = base64_decode($encodedIdeaId, true);
if ($ideaId === false || !ctype_digit($ideaId)) {
    http_response_code(400);
    exit('Invalid idea request.');
}

$ideaStatement = $conn->prepare('SELECT * FROM business_ideas WHERE id = ? LIMIT 1');
$ideaStatement->bind_param('i', $ideaId);
$ideaStatement->execute();
$idea = $ideaStatement->get_result()->fetch_assoc();
$ideaStatement->close();
if (!$idea || ($role === 'user' && $idea['status'] !== 'Approved')) {
    http_response_code(404);
    exit('Idea not found.');
}

$agentAccountId = (int)$idea['user_id'];
$isOwner = $role === 'agent' && $agentAccountId === $accountId;
$encodedAgentId = base64_encode((string)$agentAccountId);
$encodedIdeaId = base64_encode((string)$idea['id']);
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$alert = $_GET['alert'] ?? '';

$profileStatement = $conn->prepare('SELECT * FROM profiles WHERE agent_account_id = ? LIMIT 1');
$profileStatement->bind_param('i', $agentAccountId);
$profileStatement->execute();
$agent = $profileStatement->get_result()->fetch_assoc();
$profileStatement->close();

$stats = null;
$hasLiked = false;
$isFollowing = false;
$hasRated = false;
$viewerCanEngage = $agent && $role !== 'admin' && !$isOwner;
if ($agent) {
    $statsStatement = $conn->prepare('SELECT likes, followers, rating FROM engagement_stats WHERE agent_profile_id = ?');
    $statsStatement->bind_param('i', $agentAccountId);
    $statsStatement->execute();
    $stats = $statsStatement->get_result()->fetch_assoc();
    $statsStatement->close();
}
if ($viewerCanEngage) {
    $engagementChecks = [
        ['user_likes', 'id', &$hasLiked],
        ['user_follows', 'id', &$isFollowing],
        ['user_ratings', 'id', &$hasRated],
    ];
    foreach ($engagementChecks as [$table, $column, &$resultFlag]) {
        $checkStatement = $conn->prepare("SELECT $column FROM $table WHERE user_id = ? AND agent_profile_id = ? LIMIT 1");
        $checkStatement->bind_param('ii', $accountId, $agentAccountId);
        $checkStatement->execute();
        $resultFlag = $checkStatement->get_result()->num_rows > 0;
        $checkStatement->close();
    }
}

$reviewsStatement = $conn->prepare('SELECT CONCAT(accounts.first_name, \' \', accounts.last_name) AS sender_name, reviews.comment_content, reviews.submission_date FROM reviews INNER JOIN accounts ON accounts.id = reviews.user_id WHERE reviews.business_idea_id = ? ORDER BY reviews.submission_date DESC');
$reviewsStatement->bind_param('i', $ideaId);
$reviewsStatement->execute();
$reviews = $reviewsStatement->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idea Details</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/idea_details.css">
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
            <?php if ($alert !== ''): ?>
                <p id="alert_message"><?php echo $escape($alert); ?></p>
            <?php endif; ?>

            <?php if ($agent): ?>
                <div class="profile_section">
                    <div class="profile_card">
                        <div class="profile_image"><img src="../../uploads/profiles/<?php echo rawurlencode($agent['profile_image']); ?>" alt="Agent profile"></div>
                        <div class="profile_info">
                            <h2><?php echo $escape($agent['agent_f_name'] . ' ' . $agent['agent_l_name']); ?></h2>
                            <p><strong>Email: </strong><?php echo $escape($agent['agent_email']); ?></p>
                            <p><strong>Phone: </strong><?php echo $escape($agent['contact']); ?></p>
                            <p><strong>Location: </strong><?php echo $escape($agent['city']); ?></p>
                            <p><strong>Bio: </strong><?php echo $escape($agent['agent_bio']); ?></p>
                        </div>
                    </div>
                    <?php if ($stats): ?>
                        <div class="engagement_stats">
                            <div class="stat_box">⭐ <span><?php echo number_format((float)$stats['rating'], 1); ?></span><p>Rating</p></div>
                            <div class="stat_box">👍 <span><?php echo (int)$stats['likes']; ?></span><p>Likes</p></div>
                            <div class="stat_box">👥 <span><?php echo (int)$stats['followers']; ?></span><p>Followers</p></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($viewerCanEngage): ?>
                        <div class="profile_actions">
                            <?php if ($hasLiked): ?>
                                <button class="like_btn liked" disabled>You Liked</button>
                            <?php else: ?>
                                <a href="../actions/like.php?agent_profile_id=<?php echo $agentAccountId; ?>" class="like_btn">Like</a>
                            <?php endif; ?>
                            <?php if ($isFollowing): ?>
                                <button class="follow_btn following" disabled>Following</button>
                            <?php else: ?>
                                <a href="../actions/follow.php?agent_profile_id=<?php echo $agentAccountId; ?>" class="follow_btn">Follow</a>
                            <?php endif; ?>
                            <?php if (!$hasRated): ?>
                                <div class="rating_section">
                                    <h3>Rate this mentor</h3>
                                    <div class="stars">
                                        <?php for ($rating = 1; $rating <= 5; $rating++): ?>
                                            <button type="button" class="star" data-rating="<?php echo $rating; ?>" aria-label="Rate <?php echo $rating; ?> out of 5">★</button>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="business_details">
                <h1><?php echo $escape($idea['idea_title']); ?></h1>
                <?php foreach ([
                    'Business Category' => 'idea_category',
                    'Business Stage' => 'idea_stage',
                    'Problem Statement' => 'problem_statement',
                    'Proposed Solutions' => 'problem_solution',
                    'Unique Value Proposition' => 'proposition_value',
                    'Target Market' => 'target_market',
                    'Market Size' => 'market_size',
                    'Business Model' => 'business_model',
                    'Required Resources' => 'resources',
                    'Expected Outcome' => 'outcomes',
                ] as $label => $field): ?>
                    <div class="field_set">
                        <h2><?php echo $escape($label); ?></h2>
                        <p><?php echo nl2br($escape($idea[$field])); ?></p>
                    </div>
                <?php endforeach; ?>

                <div class="profile_actions">
                    <?php if ($role === 'admin'): ?>
                        <?php if ($idea['status'] === 'Pending'): ?>
                            <a href="../admin/approve_idea.php?business_idea_id=<?php echo rawurlencode($encodedIdeaId); ?>" class="approve_btn">Approve</a>
                            <a href="../admin/reject_idea.php?business_idea_id=<?php echo rawurlencode($encodedIdeaId); ?>" class="reject_btn">Reject</a>
                        <?php else: ?>
                            <a href="../admin/delete_idea.php?business_idea_id=<?php echo rawurlencode($encodedIdeaId); ?>" class="block_btn">Delete</a>
                        <?php endif; ?>
                        <a href="ideas_list.php" class="back_btn">Back to ideas</a>
                    <?php elseif ($isOwner): ?>
                        <a href="../mentor/update_idea.php?business_idea_id=<?php echo rawurlencode($encodedIdeaId); ?>" class="approve_btn">Update</a>
                        <a href="../admin/delete_idea.php?business_idea_id=<?php echo rawurlencode($encodedIdeaId); ?>" class="reject_btn">Delete</a>
                        <a href="../profiles/selected_agent_ideas.php?agent_profile_id=<?php echo rawurlencode($encodedAgentId); ?>" class="back_btn">Back to ideas</a>
                    <?php else: ?>
                        <a href="../profiles/selected_agent_ideas.php?agent_profile_id=<?php echo rawurlencode($encodedAgentId); ?>" class="back_btn">Back to ideas</a>
                    <?php endif; ?>
                </div>
            </div>

            <section class="comment_section">
                <h1>Reviews</h1>
                <div class="comments">
                    <?php if ($reviews->num_rows === 0): ?>
                        <p>No reviews yet.</p>
                    <?php else: ?>
                        <?php while ($review = $reviews->fetch_assoc()): ?>
                            <div class="comment">
                                <div class="image"><img src="../../public/assets/images/profile.png" alt=""></div>
                                <div class="comment_content">
                                    <h2><?php echo $escape($review['sender_name']); ?></h2>
                                    <p><?php echo nl2br($escape($review['comment_content'])); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
                <?php if ($role !== 'admin'): ?>
                    <form class="add_comment" action="../actions/submit_review.php?business_idea_id=<?php echo rawurlencode($encodedIdeaId); ?>&amp;agent_profile_id=<?php echo rawurlencode($encodedAgentId); ?>" method="post">
                        <textarea name="review" placeholder="Add your comment here..." required></textarea>
                        <button type="submit">Submit</button>
                    </form>
                <?php endif; ?>
            </section>
        </section>
    </div>
    <section id="footer"><p>© 2025 Entrepreneur Portal. All Rights Reserved.</p></section>
    <script src="../../public/assets/JS/siderbar.js"></script>
    <?php $reviewsStatement->close(); ?>
    <script>
        document.querySelectorAll('.star[data-rating]').forEach((star) => {
            star.addEventListener('click', () => {
                const rating = star.dataset.rating;
                            window.location.href = '../actions/rate.php?id=<?php echo $agentAccountId; ?>&rating=' + encodeURIComponent(rating);
            });
        });
    </script>
</body>
</html>