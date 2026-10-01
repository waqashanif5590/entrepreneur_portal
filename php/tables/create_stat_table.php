<?php
include dirname(dirname(__DIR__)) . '/database.php';
$sql = "CREATE TABLE IF NOT EXISTS engagement_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_profile_id INT NOT NULL,
    likes INT DEFAULT 0,
    followers INT DEFAULT 0,
    rating FLOAT DEFAULT 0,
    total_ratings INT DEFAULT 0,
    UNIQUE (agent_profile_id)
)";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die('Error creating table ' . mysqli_error($conn));
}
