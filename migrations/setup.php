<?php
// db_setup.php - Full Auto Setup for Entrepreneur Portal
// This file creates database + all tables + default admin user

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
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `first_name` VARCHAR(255) NOT NULL,
        `last_name` VARCHAR(255) NOT NULL,
        `entity_type` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `status` VARCHAR(255) NOT NULL DEFAULT 'Active',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB",

    // 2. Agent Profiles Table
    "CREATE TABLE IF NOT EXISTS `profiles` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `agent_f_name` VARCHAR(255) NOT NULL,
        `agent_l_name` VARCHAR(255) NOT NULL,
        `dob` DATE NOT NULL,
        `country` VARCHAR(255) NOT NULL,
        `city` VARCHAR(255) NOT NULL,
        `contact` VARCHAR(20) NOT NULL,
        `agent_email` VARCHAR(255) NOT NULL,
        `field_expertise` VARCHAR(255) NOT NULL,
        `experience` INT NOT NULL,
        `org_name` VARCHAR(255) NOT NULL,
        `agent_weblink` VARCHAR(255) NOT NULL,
        `agent_bio` TEXT NOT NULL,
        `agent_account_id` INT UNSIGNED NOT NULL,
        `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `profile_image` VARCHAR(255) NOT NULL,
        `cnic` VARCHAR(255) NULL,
        `resume` VARCHAR(255) NOT NULL,
        `certificate` VARCHAR(255) NOT NULL,
        `status` VARCHAR(255) NOT NULL DEFAULT 'Pending',
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_profile_account` (`agent_account_id`),
        CONSTRAINT `fk_profiles_account` FOREIGN KEY (`agent_account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB",

    // 3. Business Ideas Table
    "CREATE TABLE IF NOT EXISTS `business_ideas` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
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
        `user_id` INT UNSIGNED NOT NULL,
        `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `status` VARCHAR(255) NOT NULL DEFAULT 'Pending',
        PRIMARY KEY (`id`),
        KEY `idx_business_ideas_user` (`user_id`),
        CONSTRAINT `fk_business_ideas_user` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB",

    // 4. Reviews Table
    "CREATE TABLE IF NOT EXISTS `reviews` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `comment_content` TEXT NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `business_idea_id` INT UNSIGNED NOT NULL,
        `submission_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_reviews_user` (`user_id`),
        KEY `idx_reviews_business_idea` (`business_idea_id`),
        CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_reviews_business_idea` FOREIGN KEY (`business_idea_id`) REFERENCES `business_ideas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB",

    // 5. Forums Table
    "CREATE TABLE IF NOT EXISTS `forums` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `forum_title` VARCHAR(255) NOT NULL,
        `forum_desc` TEXT NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `status` VARCHAR(255) NOT NULL DEFAULT 'Pending',
        `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_forums_user` (`user_id`),
        CONSTRAINT `fk_forums_user` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB",

    // 6. Messages Table
    "CREATE TABLE IF NOT EXISTS `messages` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `message_text` TEXT NOT NULL,
        `sender_id` INT UNSIGNED NOT NULL,
        `receiver_id` INT UNSIGNED NOT NULL,
        `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
        `sent_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_messages_sender` (`sender_id`),
        KEY `idx_messages_receiver` (`receiver_id`),
        CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB",

    // 7. Threads Table
    "CREATE TABLE IF NOT EXISTS `threads` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `comment` TEXT NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `forum_id` INT UNSIGNED NOT NULL,
        `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_threads_user` (`user_id`),
        KEY `idx_threads_forum` (`forum_id`),
        CONSTRAINT `fk_threads_user` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_threads_forum` FOREIGN KEY (`forum_id`) REFERENCES `forums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB",

    // 8. Engagement Stats Table
    "CREATE TABLE IF NOT EXISTS `engagement_stats` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `agent_profile_id` INT UNSIGNED NOT NULL,
        `likes` INT NOT NULL DEFAULT 0,
        `followers` INT NOT NULL DEFAULT 0,
        `rating` FLOAT NOT NULL DEFAULT 0,
        `total_ratings` INT NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_engagement_profile` (`agent_profile_id`),
        CONSTRAINT `fk_engagement_profile` FOREIGN KEY (`agent_profile_id`) REFERENCES `profiles` (`agent_account_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB",

    // 9. User Likes Table
    "CREATE TABLE IF NOT EXISTS `user_likes` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT UNSIGNED NOT NULL,
        `agent_profile_id` INT UNSIGNED NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_like` (`user_id`, `agent_profile_id`),
        CONSTRAINT `fk_user_likes_user` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_user_likes_profile` FOREIGN KEY (`agent_profile_id`) REFERENCES `profiles` (`agent_account_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB",

    // 10. User Follows Table
    "CREATE TABLE IF NOT EXISTS `user_follows` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT UNSIGNED NOT NULL,
        `agent_profile_id` INT UNSIGNED NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_follow` (`user_id`, `agent_profile_id`),
        CONSTRAINT `fk_user_follows_user` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_user_follows_profile` FOREIGN KEY (`agent_profile_id`) REFERENCES `profiles` (`agent_account_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB",

    // 11. User Ratings Table
    "CREATE TABLE IF NOT EXISTS `user_ratings` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT UNSIGNED NOT NULL,
        `agent_profile_id` INT UNSIGNED NOT NULL,
        `rating` TINYINT UNSIGNED NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_rating` (`user_id`, `agent_profile_id`),
        CONSTRAINT `fk_user_ratings_user` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_user_ratings_profile` FOREIGN KEY (`agent_profile_id`) REFERENCES `profiles` (`agent_account_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB"
];

function schemaHasColumn(mysqli $conn, string $table, string $column): bool
{
    $statement = $conn->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $statement->bind_param('ss', $table, $column);
    $statement->execute();
    $exists = $statement->get_result()->num_rows > 0;
    $statement->close();
    return $exists;
}

function schemaColumn(mysqli $conn, string $table, string $column): ?array
{
    $statement = $conn->prepare('SELECT COLUMN_TYPE, IS_NULLABLE, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $statement->bind_param('ss', $table, $column);
    $statement->execute();
    $columnInfo = $statement->get_result()->fetch_assoc() ?: null;
    $statement->close();
    return $columnInfo;
}

function schemaHasIndex(mysqli $conn, string $table, string $index): bool
{
    $statement = $conn->prepare('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?');
    $statement->bind_param('ss', $table, $index);
    $statement->execute();
    $exists = $statement->get_result()->num_rows > 0;
    $statement->close();
    return $exists;
}

function ensureSchemaIndex(mysqli $conn, string $table, string $index, string $definition, bool &$success): void
{
    if (!schemaHasIndex($conn, $table, $index)) {
        runSchemaStatement($conn, "ALTER TABLE `$table` ADD $definition", $success);
    }
}

function schemaHasConstraint(mysqli $conn, string $table, string $constraint): bool
{
    $statement = $conn->prepare('SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?');
    $statement->bind_param('ss', $table, $constraint);
    $statement->execute();
    $exists = $statement->get_result()->num_rows > 0;
    $statement->close();
    return $exists;
}

function runSchemaStatement(mysqli $conn, string $sql, bool &$success): bool
{
    try {
        $conn->query($sql);
        return true;
    } catch (mysqli_sql_exception $exception) {
        echo 'Schema update failed: ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '<br>';
        $success = false;
        return false;
    }
}

$success = true;

foreach ($tables as $sql) {
    if ($conn->query($sql) === FALSE) {
        echo "Error creating table: " . $conn->error . "<br>";
        $success = false;
    }
}

$tableNames = ['accounts', 'profiles', 'business_ideas', 'reviews', 'forums', 'messages', 'threads', 'engagement_stats', 'user_likes', 'user_follows', 'user_ratings'];
foreach ($tableNames as $tableName) {
    runSchemaStatement($conn, "ALTER TABLE `$tableName` ENGINE=InnoDB", $success);
}

$legacyPrimaryKeys = [
    'business_ideas' => 'idea_id',
    'reviews' => 'comment_id',
    'forums' => 'forum_id',
    'messages' => 'message_id',
    'threads' => 'comment_id'
];
foreach ($legacyPrimaryKeys as $tableName => $legacyColumn) {
    if (!schemaHasColumn($conn, $tableName, 'id') && schemaHasColumn($conn, $tableName, $legacyColumn)) {
        runSchemaStatement($conn, "ALTER TABLE `$tableName` CHANGE COLUMN `$legacyColumn` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT", $success);
    }
}

$legacyOwnerColumns = [
    'business_ideas' => 'agent_profile_id',
    'forums' => 'author_id',
    'threads' => 'comment_by_id'
];
foreach ($legacyOwnerColumns as $tableName => $legacyColumn) {
    if (!schemaHasColumn($conn, $tableName, 'user_id') && schemaHasColumn($conn, $tableName, $legacyColumn)) {
        runSchemaStatement($conn, "ALTER TABLE `$tableName` CHANGE COLUMN `$legacyColumn` `user_id` INT UNSIGNED NOT NULL", $success);
    }
}

if (!schemaHasColumn($conn, 'reviews', 'user_id')) {
    runSchemaStatement($conn, 'ALTER TABLE reviews ADD COLUMN user_id INT UNSIGNED NULL AFTER comment_content', $success);
}

$idTables = ['accounts', 'profiles', 'business_ideas', 'reviews', 'forums', 'messages', 'threads', 'engagement_stats', 'user_likes', 'user_follows', 'user_ratings'];
foreach ($idTables as $tableName) {
    $idInfo = schemaColumn($conn, $tableName, 'id');
    if ($idInfo && ($idInfo['COLUMN_TYPE'] !== 'int unsigned' || stripos($idInfo['EXTRA'], 'auto_increment') === false)) {
        runSchemaStatement($conn, "ALTER TABLE `$tableName` MODIFY COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT", $success);
    }
}

$unsignedColumns = [
    'profiles' => ['agent_account_id'],
    'business_ideas' => ['user_id'],
    'reviews' => ['user_id', 'business_idea_id'],
    'forums' => ['user_id'],
    'messages' => ['sender_id', 'receiver_id'],
    'threads' => ['user_id', 'forum_id'],
    'engagement_stats' => ['agent_profile_id'],
    'user_likes' => ['user_id', 'agent_profile_id'],
    'user_follows' => ['user_id', 'agent_profile_id'],
    'user_ratings' => ['user_id', 'agent_profile_id']
];
foreach ($unsignedColumns as $tableName => $columns) {
    foreach ($columns as $columnName) {
        $columnInfo = schemaColumn($conn, $tableName, $columnName);
        if ($columnInfo && $columnInfo['COLUMN_TYPE'] !== 'int unsigned') {
            $nullable = $columnInfo['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL';
            runSchemaStatement($conn, "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` INT UNSIGNED $nullable", $success);
        }
    }
}

$profileCnic = schemaColumn($conn, 'profiles', 'cnic');
if ($profileCnic && $profileCnic['IS_NULLABLE'] !== 'YES') {
    runSchemaStatement($conn, 'ALTER TABLE profiles MODIFY COLUMN cnic VARCHAR(255) NULL', $success);
}

$reviewsReady = true;
if (schemaHasColumn($conn, 'reviews', 'sender_name')) {
    $backfillReviews = "UPDATE reviews AS r
        INNER JOIN (
            SELECT CONCAT(first_name, ' ', last_name) AS full_name, MIN(id) AS account_id
            FROM accounts
            GROUP BY CONCAT(first_name, ' ', last_name)
            HAVING COUNT(*) = 1
        ) AS unique_accounts ON unique_accounts.full_name = r.sender_name
        SET r.user_id = unique_accounts.account_id
        WHERE r.user_id IS NULL";
    runSchemaStatement($conn, $backfillReviews, $success);
}

$unassignedReviews = $conn->query('SELECT COUNT(*) AS total FROM reviews WHERE user_id IS NULL')->fetch_assoc()['total'];
if ((int)$unassignedReviews > 0) {
    echo 'Schema update paused: ' . (int)$unassignedReviews . ' review(s) need a unique account match before sender_name can be removed.<br>';
    $reviewsReady = false;
    $success = false;
} else {
    $reviewUserInfo = schemaColumn($conn, 'reviews', 'user_id');
    if ($reviewUserInfo && $reviewUserInfo['IS_NULLABLE'] === 'YES') {
        runSchemaStatement($conn, 'ALTER TABLE reviews MODIFY COLUMN user_id INT UNSIGNED NOT NULL', $success);
    }
    if (schemaHasColumn($conn, 'reviews', 'sender_name')) {
        runSchemaStatement($conn, 'ALTER TABLE reviews DROP COLUMN sender_name', $success);
    }
}

foreach ([['forums', 'author_name'], ['threads', 'comment_by']] as [$tableName, $legacyNameColumn]) {
    if (schemaHasColumn($conn, $tableName, $legacyNameColumn)) {
        runSchemaStatement($conn, "ALTER TABLE `$tableName` DROP COLUMN `$legacyNameColumn`", $success);
    }
}

runSchemaStatement($conn, 'DELETE engagement_stats FROM engagement_stats LEFT JOIN profiles ON profiles.agent_account_id = engagement_stats.agent_profile_id WHERE profiles.agent_account_id IS NULL', $success);

$indexes = [
    ['profiles', 'unique_profile_account', 'UNIQUE KEY `unique_profile_account` (`agent_account_id`)'],
    ['business_ideas', 'idx_business_ideas_user', 'KEY `idx_business_ideas_user` (`user_id`)'],
    ['reviews', 'idx_reviews_user', 'KEY `idx_reviews_user` (`user_id`)'],
    ['reviews', 'idx_reviews_business_idea', 'KEY `idx_reviews_business_idea` (`business_idea_id`)'],
    ['forums', 'idx_forums_user', 'KEY `idx_forums_user` (`user_id`)'],
    ['messages', 'idx_messages_sender', 'KEY `idx_messages_sender` (`sender_id`)'],
    ['messages', 'idx_messages_receiver', 'KEY `idx_messages_receiver` (`receiver_id`)'],
    ['threads', 'idx_threads_user', 'KEY `idx_threads_user` (`user_id`)'],
    ['threads', 'idx_threads_forum', 'KEY `idx_threads_forum` (`forum_id`)'],
    ['engagement_stats', 'unique_engagement_profile', 'UNIQUE KEY `unique_engagement_profile` (`agent_profile_id`)'],
    ['user_likes', 'unique_like', 'UNIQUE KEY `unique_like` (`user_id`, `agent_profile_id`)'],
    ['user_follows', 'unique_follow', 'UNIQUE KEY `unique_follow` (`user_id`, `agent_profile_id`)'],
    ['user_ratings', 'unique_rating', 'UNIQUE KEY `unique_rating` (`user_id`, `agent_profile_id`)']
];
foreach ($indexes as [$tableName, $indexName, $definition]) {
    if ($tableName === 'engagement_stats' && $indexName === 'unique_engagement_profile' && schemaHasIndex($conn, 'engagement_stats', 'agent_profile_id')) {
        continue;
    }
    ensureSchemaIndex($conn, $tableName, $indexName, $definition, $success);
}

$foreignKeys = [
    ['profiles', 'fk_profiles_account', 'FOREIGN KEY (`agent_account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['business_ideas', 'fk_business_ideas_user', 'FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['reviews', 'fk_reviews_user', 'FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['reviews', 'fk_reviews_business_idea', 'FOREIGN KEY (`business_idea_id`) REFERENCES `business_ideas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['forums', 'fk_forums_user', 'FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['messages', 'fk_messages_sender', 'FOREIGN KEY (`sender_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['messages', 'fk_messages_receiver', 'FOREIGN KEY (`receiver_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['threads', 'fk_threads_user', 'FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['threads', 'fk_threads_forum', 'FOREIGN KEY (`forum_id`) REFERENCES `forums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['engagement_stats', 'fk_engagement_profile', 'FOREIGN KEY (`agent_profile_id`) REFERENCES `profiles` (`agent_account_id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['user_likes', 'fk_user_likes_user', 'FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['user_likes', 'fk_user_likes_profile', 'FOREIGN KEY (`agent_profile_id`) REFERENCES `profiles` (`agent_account_id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['user_follows', 'fk_user_follows_user', 'FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['user_follows', 'fk_user_follows_profile', 'FOREIGN KEY (`agent_profile_id`) REFERENCES `profiles` (`agent_account_id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['user_ratings', 'fk_user_ratings_user', 'FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    ['user_ratings', 'fk_user_ratings_profile', 'FOREIGN KEY (`agent_profile_id`) REFERENCES `profiles` (`agent_account_id`) ON DELETE CASCADE ON UPDATE CASCADE']
];
foreach ($foreignKeys as [$tableName, $constraintName, $definition]) {
    if ($tableName === 'reviews' && $constraintName === 'fk_reviews_user' && !$reviewsReady) {
        continue;
    }
    if (!schemaHasConstraint($conn, $tableName, $constraintName)) {
        runSchemaStatement($conn, "ALTER TABLE `$tableName` ADD CONSTRAINT `$constraintName` $definition", $success);
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
