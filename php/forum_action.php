<?php
session_start();
include '../database.php';
if (isset($_GET['action']) && isset($_GET['forum_id'])) {
    $action = $_GET['action'];
    $forum_id = base64_decode($_GET['forum_id']);

    $sql = "SELECT * FROM `forums` WHERE `forum_id`='$forum_id'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        if ($action === 'approve') {
            $sql2 = "UPDATE `forums` SET `status` = 'Approved' WHERE forum_id='$forum_id'";
            $result2 = mysqli_query($conn, $sql2);
            if ($result2) {
                $alert = "Forum was approved";
                header("Location: forum_list_admin.php?alert=$alert");
                exit();
            } else {
                die("Updation faild: " . mysqli_error($conn));
            }
        } else if ($action === 'delete') {
            $sql3 = "DELETE FROM `forums` WHERE forum_id='$forum_id'";
            $result3 = mysqli_query($conn, $sql3);
            if ($result3) {
                $inner_sql = "DELETE FROM `threads` WHERE forum_id='$forum_id'";
                $inner_result = mysqli_query($conn, $inner_sql);
                $alert = "Forum was deleted";
                header("Location: forum_list_admin.php?alert=$alert");
                exit();
            } else {
                die("Updation faild: " . mysqli_error($conn));
            }
        }
    }
}
