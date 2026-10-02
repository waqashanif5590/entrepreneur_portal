<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
$role = requireAccountRole($conn, ['admin', 'agent', 'user']);
$accountId = (int)$_SESSION['id'];
$csrfToken = csrfToken();
 $rawAuthorId = $_GET['author_id'] ?? null;
$author_id = requestPositiveId($rawAuthorId);
$forum_id = requestPositiveId($_GET['forum_id'] ?? null);
if ($author_id === null || $forum_id === null) {
    http_response_code(400);
    exit('Invalid request');
}

if ($role === 'admin') {
    $forumStatement = $conn->prepare('SELECT forums.*, CONCAT(accounts.first_name, \' \', accounts.last_name) AS author_name FROM forums INNER JOIN accounts ON accounts.id = forums.user_id WHERE forums.id = ? AND forums.user_id = ? LIMIT 1');
    $forumStatement->bind_param('ii', $forum_id, $author_id);
} elseif ($role === 'agent') {
    $forumStatement = $conn->prepare("SELECT forums.*, CONCAT(accounts.first_name, ' ', accounts.last_name) AS author_name FROM forums INNER JOIN accounts ON accounts.id = forums.user_id WHERE forums.id = ? AND forums.user_id = ? AND (forums.status = 'Approved' OR forums.user_id = ?) LIMIT 1");
    $forumStatement->bind_param('iii', $forum_id, $author_id, $accountId);
} else {
    $forumStatement = $conn->prepare("SELECT forums.*, CONCAT(accounts.first_name, ' ', accounts.last_name) AS author_name FROM forums INNER JOIN accounts ON accounts.id = forums.user_id WHERE forums.id = ? AND forums.user_id = ? AND forums.status = 'Approved' LIMIT 1");
    $forumStatement->bind_param('ii', $forum_id, $author_id);
}
$forumStatement->execute();
$forum = $forumStatement->get_result()->fetch_assoc();
$forumStatement->close();
if (!$forum) {
    http_response_code(404);
    exit('Forum not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrfToken();
    $comment = trim($_POST['comment'] ?? '');
    if ($comment === '') {
        http_response_code(400);
        exit('Comment cannot be empty.');
    }
    $insert = $conn->prepare('INSERT INTO threads (comment, user_id, forum_id, date) VALUES (?, ?, ?, CURRENT_TIMESTAMP())');
    $insert->bind_param('sii', $comment, $accountId, $forum_id);
    if (!$insert->execute()) {
        http_response_code(500);
        exit('Unable to submit comment.');
    }
    $insert->close();
}
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forums</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/forum.css">
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
            <div class="forum_header">
                <h1 class="welcome_text">Welcome to my forum </h1>
                <?php
                echo '  <h2 class="problem_title"><strong>Title: </strong>' . $escape($forum["forum_title"]) . '</h2>
                <p class="problem_desc">' . nl2br($escape($forum["forum_desc"])) . '</p>
                <p class="author"><strong>Posted by: </strong>' . $escape($forum["author_name"]) . '</p>
                <p class="post_date">' . $escape($forum["date"]) . '</p>';
                ?>
                <p class="roles">Community Guidelines:</p>
                <ul>
                    <li>Treat everyone with kindness and respect, even if you disagree.</li>
                    <li>Keep your comments on-topic and relevant to the discussion.</li>
                    <li>Avoid personal attacks, insults or harassment.</li>
                    <li>Share your thoughts and opinion in a respectful and helpful way.</li>
                    <li>Keep self-promotion and irrelevant links to a minimum.</li>
                </ul>
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo '<p id="alert_message">Comment added successfully</p>';
            }
            ?>
            <div class="new_comment">
                <h1>Share Your Opinion:</h1>
                <?php
                ?>
                <form action="./forum.php?author_id=<?php echo $author_id; ?>&forum_id=<?php echo $forum_id; ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $escape($csrfToken); ?>">
                    <div class="field">
                        <label for="comment">Write Comment</label>
                        <textarea name="comment" id="comment"></textarea>
                    </div>
                    <button type="submit">Submit</button>
                </form>
            </div>
            <div class="forum_content">
                <?php
                $threadsStatement = $conn->prepare('SELECT threads.*, CONCAT(accounts.first_name, \' \', accounts.last_name) AS comment_by FROM threads INNER JOIN accounts ON accounts.id = threads.user_id WHERE threads.forum_id = ? ORDER BY threads.date DESC');
                    $threadsStatement->bind_param('i', $forum_id);
                    $threadsStatement->execute();
                    $result = $threadsStatement->get_result();
                    $numRows = $result->num_rows;
                    if ($numRows === 0) {
                        echo '<div class="card">
                        <h2>No Comment exists</h2>
                        <p>Be the first to comment.</p>
                    </div>';
                    } else {

                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="fetched_comment">
                                    <div class="image"><img src="../../public/assets/images/profile.png" alt=""></div>
                                    <div class="comment_detail">
                                        <h1 class="participant">' . $escape($row["comment_by"]) . '</h1>
                                        <p class="comment">' . nl2br($escape($row["comment"])) . '</p>
                                    </div>
                                    <div class="date">
                                        <p>' . $escape($row["date"]) . '</p>
                                    </div>
                            </div>';
                        $threadsStatement->close();
                    }
                }
                ?>

            </div>


        </section>

    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>
</body>
<script>
    setTimeout(function() {
        var msg = document.getElementById("alert_message");
        if (msg) {
            msg.style.display = "none";
        }
    }, 3000); // 3000 milliseconds = 3 seconds
</script>

</html>