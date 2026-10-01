<?php
include dirname(dirname(__DIR__)) . '/database.php';
$sql = "CREATE TABLE IF NOT EXISTS `forums` (
`forum_id` INT NOT NULL AUTO_INCREMENT ,
`forum_title` VARCHAR(255) NOT NULL ,
`forum_desc` TEXT NOT NULL ,
`author_name` VARCHAR(255) NOT NULL,
`author_id` INT NOT NULL ,
`status` VARCHAR(255) NOT NULL DEFAULT 'Pending',
`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
      PRIMARY KEY (`forum_id`)) ENGINE = InnoDB;
";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Table creation failed: " . mysqli_error($conn));
}
