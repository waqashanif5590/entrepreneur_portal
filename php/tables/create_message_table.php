<?php
include dirname(dirname(__DIR__)) . '/database.php';

$sql = "CREATE TABLE IF NOT EXISTS `messages` (
    `message_id` INT NOT NULL AUTO_INCREMENT,
    `message_text` TEXT NOT NULL,
    `sender_id` INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
    `sent_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`message_id`)
) ENGINE = InnoDB";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die('Table creation failed: ' . mysqli_error($conn));
}
