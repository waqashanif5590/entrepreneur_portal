<?php
include dirname(dirname(__DIR__)) . '/database.php';
$sql = "CREATE TABLE IF NOT EXISTS `threads` (
`comment_id` INT NOT NULL AUTO_INCREMENT ,
`comment` TEXT NOT NULL ,
`comment_by` VARCHAR(255) NOT NULL ,
`comment_by_id` INT NOT NULL ,
`forum_id` INT NOT NULL ,
`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
PRIMARY KEY (`comment_id`)) ENGINE = InnoDB";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Error creating table: " . mysqli_error($conn));
}
