<?php
// db_setup.php - Full Auto Setup for Entrepreneur Portal
// This file creates database + all tables + default admin account

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "db_enterpreneur";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

$conn->select_db($dbname);

// All table creation queries
$tables = [

    // 1. Accounts Table
    "CREATE TABLE IF NOT EXISTS `accounts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `first_name` VARCHAR(255) NOT NULL,
        `last_name` VARCHAR(255) NOT NULL,
        `entity_type` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `status` VARCHAR(255) NOT NULL DEFAULT 'Active'
    )",

    // 2. Reviews Table
    "CREATE TABLE IF NOT EXISTS `reviews` (
        `comment_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `comment_content` TEXT NOT NULL,
        `sender_name` VARCHAR(255) NOT NULL,
        `business_idea_id` INT NOT NULL,
        `submission_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    // 3. Forums Table
    "CREATE TABLE IF NOT EXISTS `forums` (
        `forum_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `forum_title` VARCHAR(255) NOT NULL,
        `forum_desc` TEXT NOT NULL,
        `author_name` VARCHAR(255) NOT NULL,
        `author_id` INT NOT NULL,
        `status` VARCHAR(255) NOT NULL DEFAULT 'Pending',
        `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    // 4. Business Ideas Table
    "CREATE TABLE IF NOT EXISTS `business_ideas` (
        `idea_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `idea_title` VARCHAR(255) NOT NULL,
        `idea_category` VARCHAR(255) NOT NULL,
        `idea_stage` VARCHAR(255) NOT NULL,
        `problem_statement` TEXT NOT NULL,
        `problem_solution` TEXT NOT NULL,
        `proposition_value` TEXT NOT NULL,
        `target_market` TEXT NOT NULL,
        `market_size` VARCHAR(255) NOT NULL,
        `business_model` TEXT NOT NULL,
        `resources` TEXT NOT NULL,
        `outcomes` TEXT NOT NULL,
        `keywords` VARCHAR(255) NOT NULL,
        `visibility` VARCHAR(255) NOT NULL,
        `agent_profile_id` INT(11) NOT NULL,
        `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `status` VARCHAR(255) NOT NULL DEFAULT 'Pending'
    )",

    // 5. Messages Table
    "CREATE TABLE IF NOT EXISTS `messages` (
        `message_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `message_text` TEXT NOT NULL,
        `sender_id` INT NOT NULL,
        `receiver_id` INT NOT NULL,
        `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
        `sent_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    // 6. Profiles Table
    "CREATE TABLE IF NOT EXISTS `profiles` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `agent_f_name` VARCHAR(255) NOT NULL,
        `agent_l_name` VARCHAR(255) NOT NULL,
        `dob` DATE NOT NULL,
        `country` VARCHAR(255) NOT NULL,
        `city` VARCHAR(255) NOT NULL,
        `contact` VARCHAR(20) NOT NULL,
        `agent_email` VARCHAR(255) NOT NULL,
        `field_expertise` VARCHAR(255) NOT NULL,
        `experience` INT(11) NOT NULL,
        `org_name` VARCHAR(255) NOT NULL,
        `agent_weblink` VARCHAR(255) NOT NULL,
        `agent_bio` TEXT NOT NULL,
        `agent_account_id` INT(11) NOT NULL,
        `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `profile_image` VARCHAR(255) NOT NULL,
        `cnic` VARCHAR(255) NOT NULL,
        `resume` VARCHAR(255) NOT NULL,
        `certificate` VARCHAR(255) NOT NULL,
        `status` VARCHAR(255) NOT NULL DEFAULT 'Pending'
    )",

    // 7. Engagement Stats Table
    "CREATE TABLE IF NOT EXISTS `engagement_stats` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `agent_profile_id` INT NOT NULL,
        `likes` INT DEFAULT 0,
        `followers` INT DEFAULT 0,
        `rating` FLOAT DEFAULT 0,
        `total_ratings` INT DEFAULT 0,
        UNIQUE (agent_profile_id)
    )",

    // 8. Threads Table
    "CREATE TABLE IF NOT EXISTS `threads` (
        `comment_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `comment` TEXT NOT NULL,
        `comment_by` VARCHAR(255) NOT NULL,
        `comment_by_id` INT NOT NULL,
        `forum_id` INT NOT NULL,
        `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    // 9. User Likes Table
    "CREATE TABLE IF NOT EXISTS `user_likes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `agent_profile_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_like` (`user_id`, `agent_profile_id`)
    ) ENGINE=InnoDB",

    // 10. User Follows Table
    "CREATE TABLE IF NOT EXISTS `user_follows` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `agent_profile_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_follow` (`user_id`, `agent_profile_id`)
    ) ENGINE=InnoDB",

    // 11. User Ratings Table
    "CREATE TABLE IF NOT EXISTS `user_ratings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `agent_profile_id` INT NOT NULL,
        `rating` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_rating` (`user_id`, `agent_profile_id`)
    ) ENGINE=InnoDB"
];

$success = true;

foreach ($tables as $sql) {
    if ($conn->query($sql) === FALSE) {
        echo "Error creating table: " . $conn->error . "<br>";
        $success = false;
    }
}

// Add missing columns to profiles table if they don't exist
$alter_queries = [
    "ALTER TABLE profiles ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) NOT NULL",
    "ALTER TABLE profiles ADD COLUMN IF NOT EXISTS cnic VARCHAR(255) NOT NULL",
    "ALTER TABLE profiles ADD COLUMN IF NOT EXISTS resume VARCHAR(255) NOT NULL",
    "ALTER TABLE profiles ADD COLUMN IF NOT EXISTS certificate VARCHAR(255) NOT NULL"
];

foreach ($alter_queries as $sql) {
    if ($conn->query($sql) === FALSE) {
        echo "Error altering table: " . $conn->error . "<br>";
        $success = false;
    }
}

if ($success) {
    // Create default Admin account (only if it doesn't already exist)
    $admin_email = "admin@example.com";
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);

    $check = $conn->query("SELECT id FROM `accounts` WHERE email = '$admin_email' LIMIT 1");

    if ($check->num_rows == 0) {
        $insert_admin = "INSERT INTO `accounts` 
                        (`first_name`, `last_name`, `entity_type`, `email`, `password`, `status`) 
                        VALUES ('Admin', 'User', 'admin', '$admin_email', '$admin_password', 'Active')";
        $conn->query($insert_admin);
    }

    // Redirect to index.php after successful setup
    header("Location: index.php");
    exit;
} else {
    echo "<h3 style='color:red; text-align:center; margin-top:50px;'>
            ⚠️ Some tables failed to create. Please check the errors above.
          </h3>";
}

$conn->close();
