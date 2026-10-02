<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

$role = requireAccountRole($conn, ['admin', 'agent', 'user']);
$accountId = (int)$_SESSION['id'];

$statusFilter = '';
$forums = false;
$agentProfileStatus = null;
if ($role === 'admin') {
    $requestedStatus = $_GET['status'] ?? '';
    if (in_array($requestedStatus, ['Pending', 'Approved'], true)) {
        $statusFilter = $requestedStatus;
        $statement = $conn->prepare('SELECT id AS forum_id, user_id AS author_id, forum_title, forum_desc, status, date FROM forums WHERE status = ? ORDER BY date DESC');
        $statement->bind_param('s', $statusFilter);
    } else {
        $statement = $conn->prepare('SELECT id AS forum_id, user_id AS author_id, forum_title, forum_desc, status, date FROM forums ORDER BY date DESC');
    }
    $statement->execute();
    $forums = $statement->get_result();
    $statement->close();
} elseif ($role === 'agent') {
    $profileStatement = $conn->prepare('SELECT status FROM profiles WHERE agent_account_id = ? LIMIT 1');
    $profileStatement->bind_param('i', $accountId);
    $profileStatement->execute();
    $agentProfile = $profileStatement->get_result()->fetch_assoc();
    $agentProfileStatus = $agentProfile['status'] ?? null;
    $profileStatement->close();

    $statement = $conn->prepare('SELECT id AS forum_id, user_id AS author_id, forum_title, forum_desc, status, date FROM forums WHERE user_id = ? ORDER BY date DESC');
    $statement->bind_param('i', $accountId);
    $statement->execute();
    $forums = $statement->get_result();
    $statement->close();
} else {
    $statement = $conn->prepare("SELECT id AS forum_id, user_id AS author_id, forum_title, forum_desc, status, date FROM forums WHERE status = 'Approved' ORDER BY date DESC");
    $statement->execute();
    $forums = $statement->get_result();
    $statement->close();
}

$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$csrfToken = csrfToken();
$alert = $_GET['alert'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum List</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/forum_list.css">
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
            <div class="profile_section">
                <div class="forum_header">
                    <h1 class="welcome_text">
                        <?php echo $role === 'admin' ? 'Forum moderation' : ($role === 'agent' ? 'My forums' : 'Welcome to forums'); ?>
                    </h1>
                    <?php if ($role === 'admin'): ?>
                        <p>Review new forums and manage published discussions.</p>
                        <div class="card_bottom">
                            <a class="explore_btn" href="forum_list_shared.php">All forums</a>
                            <a class="explore_btn" href="forum_list_shared.php?status=Pending">Pending</a>
                            <a class="explore_btn" href="forum_list_shared.php?status=Approved">Approved</a>
                        </div>
                    <?php else: ?>
                        <p class="roles">Community Guidelines:</p>
                        <ul>
                            <li>Treat everyone with kindness and respect, even when you disagree.</li>
                            <li>Keep comments on-topic and relevant to the discussion.</li>
                            <li>Avoid personal attacks, insults, and harassment.</li>
                            <li>Share thoughts respectfully and helpfully.</li>
                            <li>Keep self-promotion and irrelevant links to a minimum.</li>
                        </ul>
                    <?php endif; ?>
                </div>

                <?php if ($alert !== ''): ?>
                    <p id="alert_message"><?php echo $escape($alert); ?></p>
                <?php elseif ($role === 'agent' && $agentProfileStatus === 'Pending'): ?>
                    <p id="alert_message">Your profile is pending for admin approval. Please wait for admin response</p>
                <?php endif; ?>

                <h1>List of forums</h1>
                <div class="forums_list">
                    <?php if ($forums && $forums->num_rows > 0): ?>
                        <?php while ($forum = $forums->fetch_assoc()): ?>
                            <?php
                            $authorId = (int)$forum['author_id'];
                            $forumId = (int)$forum['forum_id'];
                            ?>
                            <div class="card">
                                <div class="card_top">
                                    <h2 class="problem_title"><?php echo $escape($forum['forum_title']); ?></h2>
                                    <?php if ($role === 'admin' || $role === 'agent'): ?>
                                        <span class="status badge_<?php echo $escape($forum['status']); ?>"><?php echo $escape($forum['status']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="problem_statement"><?php echo $escape($forum['forum_desc']); ?></p>
                                <div class="card_bottom">
                                    <a href="forum.php?author_id=<?php echo $authorId; ?>&forum_id=<?php echo $forumId; ?>" class="explore_btn">Explore</a>
                                    <?php if ($role === 'admin'): ?>
                                        <?php if ($forum['status'] === 'Pending'): ?>
                                            <form action="../admin/forum_action.php" method="post"><input type="hidden" name="csrf_token" value="<?php echo $escape($csrfToken); ?>"><input type="hidden" name="action" value="approve"><input type="hidden" name="forum_id" value="<?php echo $forumId; ?>"><button class="approve_btn" type="submit">Approve</button></form>
                                        <?php endif; ?>
                                        <form action="../admin/forum_action.php" method="post"><input type="hidden" name="csrf_token" value="<?php echo $escape($csrfToken); ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="forum_id" value="<?php echo $forumId; ?>"><button class="delete_btn" type="submit">Delete</button></form>
                                    <?php endif; ?>
                                    <span class="date"><?php echo $escape($forum['date']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card empty_state">
                            <h2 class="problem_title">No forums found</h2>
                            <?php if ($role === 'agent'): ?>
                                <p class="problem_statement">You have not created a forum yet.</p>
                                <?php if ($agentProfileStatus === 'Approved'): ?>
                                    <a href="../mentor/create_forum.php" class="explore_btn">Create a forum</a>
                                <?php endif; ?>
                            <?php elseif ($role === 'admin'): ?>
                                <p class="problem_statement">There are no forums for this selection.</p>
                            <?php else: ?>
                                <p class="problem_statement">No approved forums are available yet.</p>
                                <a href="../entrepreneur/user_portal.php" class="explore_btn">Back to home</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
    <section id="footer"><p>© 2025 Entrepreneur Portal. All Rights Reserved.</p></section>
    <script src="../../public/assets/JS/siderbar.js"></script>
</body>
</html>