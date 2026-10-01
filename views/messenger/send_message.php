<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['id'])) {
    exit;
}

$my_id = $_SESSION['id'];
$msg = mysqli_real_escape_string($conn, $_POST['message']);
$chat_with = $_POST['chat_with'];

mysqli_query(
    $conn,
    "INSERT INTO messages (message_text, sender_id, receiver_id)
     VALUES ('$msg', '$my_id', '$chat_with')"
);
