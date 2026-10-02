<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || empty($_SESSION['id'])) {
    header('Location: ../auth/login_dashboard.php');
    exit;
}

$accountId = (int)$_SESSION['id'];
$profileStatement = $conn->prepare('SELECT status FROM profiles WHERE agent_account_id = ? LIMIT 1');
$profileStatement->bind_param('i', $accountId);
$profileStatement->execute();
$agentProfile = $profileStatement->get_result()->fetch_assoc();
$profileStatement->close();

if (($agentProfile['status'] ?? null) !== 'Approved') {
    if (($agentProfile['status'] ?? null) === 'Pending') {
        $message = 'Your profile is pending for admin approval. Please wait for admin response';
        header('Location: ../community/forum_list_shared.php?alert=' . urlencode($message));
    } elseif ($agentProfile === null) {
        header('Location: create_profile.php');
    } else {
        $message = 'Your profile must be approved by an admin before you can create a forum.';
        header('Location: ../community/forum_list_shared.php?alert=' . urlencode($message));
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Forum</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/create_forum.css">
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
                <h1 class="welcome_text">Welcome to Forum dashboard</h1>
                <p>You can create new forum or explore previous one.</p>
                <?php
                $author_id = $_SESSION["id"];
                ?>
                <a href="../community/forum_list_shared.php">Explore Forum</a>
                <p>Guidelines to start a discussion:</p>
                <ul>
                    <li>Ensure the discussion topic is relevant to the forum's theme and audience.</li>
                    <li>Clearly state the problem statement or question in a concise manner.</li>
                    <li>Avoid duplicating existing discussion or topics.</li>
                    <li>Use a clear and descriptive title, and format the problem statement with proper headings and paragraphs.</li>
                    <li>Provide accurate and up-to-date information in the problem statement.</li>
                    <li>Maintain a respectful tone and language in the discussion topic.</li>
                </ul>
            </div>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $title = $_POST['title'];
                $desc = $_POST['statement'];
                $author_id = $_SESSION['id'];
                $firstname = $_SESSION['firstname'];
                $lastname = $_SESSION['lastname'];
                $sql = "INSERT INTO `forums` (`forum_title`, `forum_desc`, `user_id`, `date`) VALUES ('$title', '$desc', '$author_id', current_timestamp())";
                $result = mysqli_query($conn, $sql);
                if ($result) {
                    echo '<p id="alert_message">Forum added successfully</p>';
                } else {
                    die("Error: " . mysqli_error($conn));
                }
            }
            ?>
            <div class="new_comment">
                <h1>Start a new discussion:</h1>
                <form action="./create_forum.php" method="post">
                    <div class="field">
                        <label for="comment">Problem Title</label>
                        <input type="text" name="title" id="title">
                    </div>
                    <div class="field">
                        <label for="comment">Problem Statement</label>
                        <textarea name="statement" id="statement"></textarea>
                    </div>
                    <button type="submit">Submit</button>
                </form>
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