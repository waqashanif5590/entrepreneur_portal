<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['id'])) {
    die("Login required");
}
?>
<!DOCTYPE html> 
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messanger</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/messanger.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../components/_header.php'; ?>
    <div id="container">
        <?php include __DIR__ . '/../../components/_sidebar.php'; ?>
        <section id="main_content">
            <div class="title_bar">
                <h1>Welcome to Direct Message Portal (DMP)</h1>
                <p>This Chat is end-to-end encrypted. No third party can access this chat.</p>
            </div>

            <div class="search_bar">
                <form action="" method="get">
                    <input type="text" name="q" id="search" placeholder="Search mentors or users"
                        value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button id="search_button"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <?php


            $my_id = $_SESSION['id'];

            // Get my account type
            $q = mysqli_query($conn, "SELECT entity_type FROM accounts WHERE id='$my_id'");
            $me = mysqli_fetch_assoc($q);
            $type = $me['entity_type'];

            // Get search term
            $search = "";
            if (isset($_GET['q']) && trim($_GET['q']) != "") {
                $search = mysqli_real_escape_string($conn, trim($_GET['q']));
            }
            ?>

            <div class="mentors_container">

                <?php
                /* ==============================
   IF USER → SHOW ALL AGENTS
================================*/
                if ($type == 'user') {

                    $sql = "SELECT a.id, a.first_name, a.last_name, p.profile_image 
            FROM accounts a
            JOIN profiles p ON a.id = p.agent_account_id
            WHERE a.entity_type='agent' AND p.status='Approved'";

                    if ($search != "") {
                        $sql .= " AND (a.first_name LIKE '%$search%' 
                  OR a.last_name LIKE '%$search%')";
                    }

                    $result = mysqli_query($conn, $sql);
                    $found = false;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $found = true;
                        $profile_image = !empty($row['profile_image']) ? '../../uploads/profiles/' . htmlspecialchars($row['profile_image']) : '../../public/assets/images/profile.png';
                        $agent_name = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                        echo '
        <a href="inbox.php?user_id=' . (int)$row['id'] . '" class="mentor">
            <div class="profile_image">
                <img src="' . $profile_image . '" alt="' . $agent_name . '">
            </div>
            <p class="mentor_name">' . $agent_name . '</p>
        </a>';
                    }
                    if (!$found) {
                        echo '<p>No agents found</p>';
                    }
                }

                /* ====================================
   IF AGENT → SHOW USERS WHO MESSAGED HIM
=====================================*/
                if ($type == 'agent') {

                    $sql = "SELECT DISTINCT sender_id 
            FROM messages 
            WHERE receiver_id='$my_id'";

                    $result = mysqli_query($conn, $sql);

                    $found = false;
                    while ($r = mysqli_fetch_assoc($result)) {

                        $uid = $r['sender_id'];

                        $user_sql = "SELECT id, first_name, last_name 
                     FROM accounts 
                     WHERE id='$uid'";

                        if ($search != "") {
                            $user_sql .= " AND (first_name LIKE '%$search%' 
                          OR last_name LIKE '%$search%')";
                        }

                        $u_result = mysqli_query($conn, $user_sql);

                        if (mysqli_num_rows($u_result) > 0) {

                            $found = true;
                            $u = mysqli_fetch_assoc($u_result);

                            echo '
        <a href="inbox.php?user_id=' . (int)$uid . '" class="mentor">
         <div class="profile_image">
                        <img src="../../public/assets/images/profile.png" alt="">
                    </div>
            <p class="mentor_name">' . $u['first_name'] . ' ' . $u['last_name'] . '</p>
        </a>';
                        }
                    }
                    if (!$found) {
                        echo "<p>No users found</p>";
                    }
                }
                ?>

            </div>

        </section>

    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>