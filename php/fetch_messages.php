<?php
session_start();
include '../database.php';

if (!isset($_SESSION['id'])) {
    exit;
}

$my_id = $_SESSION['id'];
$chat_with = $_GET['chat_with'];

$sql = "SELECT * FROM messages
        WHERE (sender_id='$my_id' AND receiver_id='$chat_with')
           OR (sender_id='$chat_with' AND receiver_id='$my_id')
        ORDER BY sent_date ASC";

$result = mysqli_query($conn, $sql);

$output = "";

while ($row = mysqli_fetch_assoc($result)) {

    if ($row['sender_id'] == $my_id) {
        $output .= "<div class='sent_message'>
                        <p>{$row['message_text']}</p>
                    </div>";
    } else {
        $output .= "<div class='received_message'>
                        <p>{$row['message_text']}</p>
                    </div>";
    }
}

echo $output;
