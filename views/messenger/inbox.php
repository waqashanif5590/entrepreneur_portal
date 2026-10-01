<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['id'])) {
    die("Login required");
}

$chat_with = base64_decode($_GET['user_id']);

$recipient_image = '../../public/assets/images/profile.png';
$my_id = $_SESSION['id'];
$my_type = '';
$chat_with_type = '';

$my_type_query = mysqli_query($conn, "SELECT entity_type FROM accounts WHERE id='$my_id'");
if ($my_type_query) {
    $my_type_row = mysqli_fetch_assoc($my_type_query);
    $my_type = $my_type_row['entity_type'] ?? '';
}

$chat_with_query = mysqli_query($conn, "SELECT entity_type FROM accounts WHERE id='$chat_with'");
if ($chat_with_query) {
    $chat_with_row = mysqli_fetch_assoc($chat_with_query);
    $chat_with_type = $chat_with_row['entity_type'] ?? '';
}

if ($my_type === 'user' && $chat_with_type === 'agent') {
    $profile_query = mysqli_query($conn, "SELECT profile_image FROM profiles WHERE agent_account_id='$chat_with' LIMIT 1");
    if ($profile_query) {
        $profile_row = mysqli_fetch_assoc($profile_query);
        if (!empty($profile_row['profile_image'])) {
            $recipient_image = '../../uploads/profiles/' . htmlspecialchars($profile_row['profile_image']);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/messanger.css">
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
            <div class="title_bar">
                <h1>Welcome to Direct Message Portal (DMP)</h1>
                <p>This Chat is end-to-end encrypted. No third party can access this chat.</p>
            </div>
            <div class="recipient">
                <a href="./messanger.php" class="back_btn"><i class="fas fa-arrow-left"></i>Back</a>
                <div class="recipient_profile_image">
                    <img src="<?php echo $recipient_image; ?>" alt="">
                </div>
                <h2>
                    <?php
                    $sql_name = "SELECT first_name, last_name 
             FROM accounts 
             WHERE id='$chat_with'";

                    $result_name = mysqli_query($conn, $sql_name);
                    $row_name = mysqli_fetch_assoc($result_name);

                    if ($row_name) {
                        echo $row_name['first_name'] . ' ' . $row_name['last_name'];
                    } else {
                        echo "Unknown User";
                    }
                    ?>
            </div>
            <?php

            if (!isset($_GET['user_id'])) {
                die("No chat selected");
            }

            if (!is_numeric($chat_with)) {
                die("Invalid user");
            }

            /* =============================
   SEND MESSAGE
============================= */
            if (isset($_POST['new_message']) && trim($_POST['new_message']) != '') {

                $msg = mysqli_real_escape_string($conn, $_POST['new_message']);

                mysqli_query(
                    $conn,
                    "INSERT INTO messages(message_text,sender_id,receiver_id)
     VALUES('$msg','$my_id','$chat_with')"
                );
            }

            /* =============================
   LOAD CHAT HISTORY
============================= */
            $sql = "SELECT * FROM messages
        WHERE (sender_id='$my_id' AND receiver_id='$chat_with')
           OR (sender_id='$chat_with' AND receiver_id='$my_id')
        ORDER BY sent_date ASC";

            $result = mysqli_query($conn, $sql);
            mysqli_query(
                $conn,
                "UPDATE messages 
     SET is_read = 1 
     WHERE sender_id = '$chat_with' 
       AND receiver_id = '$my_id'"
            );

            ?>

            <div class="chat">

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <?php if ($row['sender_id'] == $my_id): ?>

                        <div class="sent_message">
                            <p><?php echo $row['message_text']; ?></p>
                        </div>

                    <?php else: ?>

                        <div class="received_message">
                            <p><?php echo $row['message_text']; ?></p>
                        </div>

                    <?php endif; ?>

                <?php endwhile; ?>
                <div class="type_new">
                    <form method="post">
                        <input type="text" name="new_message" required>
                        <button>Send</button>
                    </form>
                </div>
            </div>


        </section>

    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>