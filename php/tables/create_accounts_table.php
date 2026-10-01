<?php
include dirname(dirname(__DIR__)) . '/database.php';

$sql = "CREATE TABLE IF NOT EXISTS `accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `entity_type` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` VARCHAR(255) NOT NULL DEFAULT 'Active'
)";

$result = mysqli_query($conn, $sql);
// If error while creating table
if (!$result) {
    die("Error while creating table." . mysqli_error($conn));
}
