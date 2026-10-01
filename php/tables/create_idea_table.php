<?php
session_start();
include dirname(dirname(__DIR__)) . '/database.php';

$sql = "CREATE TABLE IF NOT EXISTS `business_ideas` (
  `idea_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `idea_title` varchar(255) NOT NULL,
  `idea_category` varchar(255) NOT NULL,
  `idea_stage` varchar(255) NOT NULL,
  `problem_statement` text NOT NULL,
  `problem_solution` text NOT NULL,
  `proposition_value` text NOT NULL,
  `target_market` text NOT NULL,
  `market_size` varchar(255) NOT NULL,
  `business_model` text NOT NULL,
  `resources` text NOT NULL,
  `outcomes` text NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `visibility` varchar(255) NOT NULL,
  `agent_profile_id` int(11) NOT NULL,
  `creation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` VARCHAR(255) NOT NULL DEFAULT 'Pending'
)";
$result = mysqli_query($conn, $sql);
// If error while creating table
if (!$result) {
  die("Error while creating table." . mysqli_error($conn));
}
