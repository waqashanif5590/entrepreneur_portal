<?php
session_start();
include dirname(dirname(__DIR__)) . '/database.php';
$sql = "CREATE TABLE IF NOT EXISTS profiles (
   id INT(11) NOT NULL AUTO_INCREMENT,
  agent_f_name VARCHAR(255) NOT NULL,
  agent_l_name VARCHAR(255) NOT NULL,
  dob DATETIME NOT NULL,
  country VARCHAR(255) NOT NULL,
  city VARCHAR(255) NOT NULL,
  contact VARCHAR(20) NOT NULL,
  agent_email VARCHAR(255) NOT NULL,
  field_expertise VARCHAR(255) NOT NULL,
  experience INT(11) NOT NULL,
  org_name VARCHAR(255) NOT NULL,
  agent_weblink VARCHAR(255) NOT NULL,
  agent_bio TEXT NOT NULL,
  agent_account_id INT(11) NOT NULL,
  creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_image` VARCHAR(255),
        `cnic` VARCHAR(255),
        `resume` VARCHAR(255),
        `certificate` VARCHAR(255),
  `status` VARCHAR(255) NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (id)
)";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Error while creating table: " . mysqli_error($conn));
}
