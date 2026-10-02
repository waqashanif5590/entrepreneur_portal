<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
 $rawAuthorId = $_GET['author_id'] ?? null;
 $rawForumId = $_GET['forum_id'] ?? null;
if (!is_string($rawAuthorId) || !preg_match('/\A[0-9]+\z/', $rawAuthorId) || (int)$rawAuthorId < 1 || (string)(int)$rawAuthorId !== ltrim($rawAuthorId, '0') ||
    !is_string($rawForumId) || !preg_match('/\A[0-9]+\z/', $rawForumId) || (int)$rawForumId < 1 || (string)(int)$rawForumId !== ltrim($rawForumId, '0')) {
    http_response_code(400);
    exit('Invalid request');
}
$author_id = (int)$rawAuthorId;
$forum_id = (int)$rawForumId;
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
                $sql = "SELECT forums.*, CONCAT(accounts.first_name, ' ', accounts.last_name) AS author_name FROM forums INNER JOIN accounts ON accounts.id = forums.user_id WHERE forums.id=$forum_id AND forums.user_id=$author_id";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '  <h2 class="problem_title"><strong>Title: </strong>' . $row["forum_title"] . '</h2>
                <p class="problem_desc">' . $row["forum_desc"] . '</p>
                <p class="author"><strong>Posted by: </strong>' . $row["author_name"] . '</p>
                <p class="post_date">' . $row["date"] . '</p>';
                }
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
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $comment = $_POST['comment'];
                    $comment_by_id = (int)$_SESSION['id'];
                    $sql = "INSERT INTO `threads` (`comment`, `user_id`, `forum_id`, `date`) VALUES ('$comment', '$comment_by_id', '$forum_id', current_timestamp())";
                    $result = mysqli_query($conn, $sql);
                    if ($result) {
                        echo '<p id="alert_message">Comment added successfully</p>';
                    } else {
                        die("Error: " . mysqli_error($conn));
                    }
                }
            }
            ?>
            <div class="new_comment">
                <h1>Share Your Opinion:</h1>
                <?php
                ?>
                <form action="./forum.php?author_id=<?php echo $author_id; ?>&forum_id=<?php echo $forum_id; ?>" method="post">
                    <div class="field">
                        <label for="comment">Write Comment</label>
                        <textarea name="comment" id="comment"></textarea>
                    </div>
                    <button type="submit">Submit</button>
                </form>
            </div>
            <div class="forum_content">
                <?php
                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                    $sql = "SELECT threads.*, CONCAT(accounts.first_name, ' ', accounts.last_name) AS comment_by FROM threads INNER JOIN accounts ON accounts.id = threads.user_id WHERE threads.forum_id='$forum_id' ORDER BY threads.date DESC";
                    $result = mysqli_query($conn, $sql);
                    $numRows = mysqli_num_rows($result);
                    if ($numRows == 0) {
                        echo '<div class="card">
                        <h2>No Comment exists</h2>
                        <p>Be the first to comment.</p>
                    </div>';
                    } else {

                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<div class="fetched_comment">
                                    <div class="image"><img src="../../public/assets/images/profile.png" alt=""></div>
                                    <div class="comment_detail">
                                        <h1 class="participant">' . $row["comment_by"] . '</h1>
                                        <p class="comment">' . $row["comment"] . '</p>
                                    </div>
                                    <div class="date">
                                        <p>' . $row["date"] . '</p>
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