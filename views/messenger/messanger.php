<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
$type = requireAccountRole($conn, ['user', 'agent']);
$my_id = (int)$_SESSION['id'];
$search = $_GET['q'] ?? '';
if (!is_string($search)) {
    http_response_code(400);
    exit('Invalid search query.');
}
$search = trim($search);
$pattern = '%' . $search . '%';
if ($type === 'user') {
    $sql = "SELECT a.id, a.first_name, a.last_name, p.profile_image
            FROM accounts a JOIN profiles p ON a.id = p.agent_account_id
            WHERE a.entity_type = 'agent' AND a.status <> 'Blocked' AND p.status = 'Approved'";
    if ($search !== '') {
        $sql .= ' AND (a.first_name LIKE ? OR a.last_name LIKE ?)';
        $statement = $conn->prepare($sql . ' ORDER BY a.first_name, a.last_name');
        $statement->bind_param('ss', $pattern, $pattern);
    } else {
        $statement = $conn->prepare($sql . ' ORDER BY a.first_name, a.last_name');
    }
} else {
    $sql = "SELECT DISTINCT a.id, a.first_name, a.last_name
            FROM accounts a JOIN messages m ON
                (m.sender_id = a.id AND m.receiver_id = ?) OR
                (m.sender_id = ? AND m.receiver_id = a.id)
            WHERE a.entity_type = 'user' AND a.status <> 'Blocked'";
    if ($search !== '') {
        $sql .= ' AND (a.first_name LIKE ? OR a.last_name LIKE ?)';
        $statement = $conn->prepare($sql . ' ORDER BY a.first_name, a.last_name');
        $statement->bind_param('iiss', $my_id, $my_id, $pattern, $pattern);
    } else {
        $statement = $conn->prepare($sql . ' ORDER BY a.first_name, a.last_name');
        $statement->bind_param('ii', $my_id, $my_id);
    }
}
$statement->execute();
$contacts = $statement->get_result();
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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
                        value="<?php echo $escape($search); ?>">
                    <button id="search_button"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="mentors_container">

                <?php
                $found = false;
                while ($row = $contacts->fetch_assoc()) {
                    $found = true;
                    $contactId = (int)$row['id'];
                    $profile_image = $type === 'user' && !empty($row['profile_image']) ? '../profiles/profile_image.php?agent_account_id=' . $contactId : '../../public/assets/images/profile.png';
                    $agent_name = $escape($row['first_name'] . ' ' . $row['last_name']);
                    echo '
        <a href="inbox.php?user_id=' . (int)$row['id'] . '" class="mentor">
            <div class="profile_image">
                <img src="' . $profile_image . '" alt="' . $agent_name . '">
            </div>
            <p class="mentor_name">' . $agent_name . '</p>
        </a>';
                }
                if (!$found) {
                    echo $type === 'user' ? '<p>No agents found</p>' : '<p>No users found</p>';
                }
                $statement->close();
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