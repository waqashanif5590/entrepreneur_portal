<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

$role = requireAccountRole($conn, ['user', 'agent']);
$csrfToken = csrfToken();

$chat_with = requestPositiveId($_GET['user_id'] ?? null);
if ($chat_with === null) {
    http_response_code(400);
    exit('Invalid user.');
}

$recipient_image = '../../public/assets/images/profile.png';
$my_id = (int)$_SESSION['id'];
if (!canAccessConversation($conn, $my_id, $role, $chat_with)) {
    http_response_code(404);
    exit('Conversation not found.');
}
$nameStatement = $conn->prepare('SELECT first_name, last_name FROM accounts WHERE id = ? LIMIT 1');
$nameStatement->bind_param('i', $chat_with);
$nameStatement->execute();
$recipient = $nameStatement->get_result()->fetch_assoc();
$nameStatement->close();
if ($role === 'user') {
    $recipient_image = '../profiles/profile_image.php?agent_account_id=' . $chat_with;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrfToken();
    $message = trim($_POST['new_message'] ?? '');
    if ($message !== '') {
        $insert = $conn->prepare('INSERT INTO messages (message_text, sender_id, receiver_id) VALUES (?, ?, ?)');
        $insert->bind_param('sii', $message, $my_id, $chat_with);
        if (!$insert->execute()) {
            http_response_code(500);
            exit('Unable to send message.');
        }
        $insert->close();
    }
}

$messagesStatement = $conn->prepare('SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_date ASC');
$messagesStatement->bind_param('iiii', $my_id, $chat_with, $chat_with, $my_id);
$messagesStatement->execute();
$messages = $messagesStatement->get_result();
$readStatement = $conn->prepare('UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?');
$readStatement->bind_param('ii', $chat_with, $my_id);
$readStatement->execute();
$readStatement->close();
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

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
                    <?php echo $escape(($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? '')); ?>
            </div>

            <div class="chat">

                <?php while ($row = $messages->fetch_assoc()): ?>

                    <?php if ($row['sender_id'] == $my_id): ?>

                        <div class="sent_message">
                            <p><?php echo $escape($row['message_text']); ?></p>
                        </div>

                    <?php else: ?>

                        <div class="received_message">
                            <p><?php echo $escape($row['message_text']); ?></p>
                        </div>

                    <?php endif; ?>

                <?php endwhile; ?>
                <div class="type_new">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $escape($csrfToken); ?>">
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