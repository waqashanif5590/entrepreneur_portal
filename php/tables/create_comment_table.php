<?php
include dirname(dirname(__DIR__)) . '/database.php';
$sql = "CREATE TABLE IF NOT EXISTS `reviews` (
`comment_id` INT NOT NULL AUTO_INCREMENT ,
`comment_content` TEXT NOT NULL ,
`sender_name` VARCHAR(255) NOT NULL ,
`business_idea_id` INT NOT NULL ,
`submission_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
PRIMARY KEY (`comment_id`)) ENGINE = InnoDB";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Error: " . mysqli_error($conn));
}
