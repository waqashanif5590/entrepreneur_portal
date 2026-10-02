<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

$role = requireAccountRole($conn, ['user', 'agent']);
$my_id = (int)$_SESSION['id'];
$chat_with = requestPositiveId($_GET['chat_with'] ?? null);
if ($chat_with === null || !canAccessConversation($conn, $my_id, $role, $chat_with)) {
    http_response_code(404);
    exit('Conversation not found.');
}
$statement = $conn->prepare('SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_date ASC');
$statement->bind_param('iiii', $my_id, $chat_with, $chat_with, $my_id);
$statement->execute();
$result = $statement->get_result();

$output = "";

while ($row = mysqli_fetch_assoc($result)) {

    if ($row['sender_id'] == $my_id) {
        $output .= "<div class='sent_message'>
                        <p>" . htmlspecialchars($row['message_text'], ENT_QUOTES, 'UTF-8') . "</p>
                    </div>";
    } else {
        $output .= "<div class='received_message'>
                        <p>" . htmlspecialchars($row['message_text'], ENT_QUOTES, 'UTF-8') . "</p>
                    </div>";
    }
}

echo $output;
