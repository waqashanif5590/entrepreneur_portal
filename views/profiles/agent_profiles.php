<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (empty($_SESSION['loggedin']) || empty($_SESSION['id'])) {
    http_response_code(403);
    exit('Please sign in to view profiles.');
}

$accountId = (int)$_SESSION['id'];
$roleStatement = $conn->prepare('SELECT entity_type FROM accounts WHERE id = ?');
$roleStatement->bind_param('i', $accountId);
$roleStatement->execute();
$account = $roleStatement->get_result()->fetch_assoc();
$roleStatement->close();
$role = $account['entity_type'] ?? '';
if (!in_array($role, ['admin', 'agent', 'user'], true)) {
    http_response_code(403);
    exit('Access denied.');
}

$status = $_GET['status'] ?? '';
if ($role === 'admin' && in_array($status, ['Pending', 'Approved', 'Blocked', 'Rejected'], true)) {
    $statement = $conn->prepare('SELECT agent_account_id, agent_f_name, agent_l_name, agent_email, profile_image, field_expertise, status FROM profiles WHERE status = ? ORDER BY agent_account_id DESC');
    $statement->bind_param('s', $status);
} elseif ($role === 'admin') {
    $statement = $conn->prepare('SELECT agent_account_id, agent_f_name, agent_l_name, agent_email, profile_image, field_expertise, status FROM profiles ORDER BY agent_account_id DESC');
} else {
    $statement = $conn->prepare("SELECT agent_account_id, agent_f_name, agent_l_name, agent_email, profile_image, field_expertise, status FROM profiles WHERE status = 'Approved' ORDER BY agent_account_id DESC");
}
$statement->execute();
$profiles = $statement->get_result();
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $role === 'admin' ? 'Agent Profiles' : 'Mentor Profiles'; ?></title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/all_agents.css">
    <link rel="stylesheet" href="../../public/assets/CSS/user_portal.css">
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
            <h2><?php echo $role === 'admin' ? 'Agents / Entrepreneurs' : 'Explore Mentors'; ?></h2>
            <?php if ($role === 'admin'): ?>
                <p>Manage agent profiles and review their approval status.</p>
                <div class="card_bottom">
                    <a class="explore_btn" href="agent_profiles.php">All profiles</a>
                    <a class="explore_btn" href="agent_profiles.php?status=Pending">Pending</a>
                    <a class="explore_btn" href="agent_profiles.php?status=Approved">Approved</a>
                    <a class="explore_btn" href="agent_profiles.php?status=Blocked">Blocked</a>
                    <a class="explore_btn" href="agent_profiles.php?status=Rejected">Rejected</a>
                </div>
                <div class="table_container">
                    <table>
                        <thead><tr><th>Profile</th><th>Full Name</th><th>Email</th><th>Status</th><th>Expertise</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php if ($profiles->num_rows > 0): ?>
                                <?php while ($profile = $profiles->fetch_assoc()): ?>
                                    <?php $encodedId = base64_encode((string)$profile['agent_account_id']); ?>
                                    <tr>
                                        <td><img src="../../uploads/profiles/<?php echo rawurlencode($profile['profile_image']); ?>" class="table_profile" alt="Profile"></td>
                                        <td><?php echo $escape($profile['agent_f_name'] . ' ' . $profile['agent_l_name']); ?></td>
                                        <td><?php echo $escape($profile['agent_email']); ?></td>
                                        <td><span class="badge <?php echo $escape($profile['status']); ?>"><?php echo $escape($profile['status']); ?></span></td>
                                        <td><?php echo $escape($profile['field_expertise']); ?></td>
                                        <td><a href="selected_agent.php?agent_account_id=<?php echo rawurlencode($encodedId); ?>">View</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6">No profiles found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="mentors_recommendations">
                    <div class="mentors_card_container">
                        <?php if ($profiles->num_rows > 0): ?>
                            <?php while ($profile = $profiles->fetch_assoc()): ?>
                                <?php $encodedId = base64_encode((string)$profile['agent_account_id']); ?>
                                <div class="mentors_card">
                                    <div class="mentor">
                                        <div class="mentor_icon"><img src="../../uploads/profiles/<?php echo rawurlencode($profile['profile_image']); ?>" alt=""></div>
                                        <div class="mentor_profile">
                                            <h2><?php echo $escape($profile['agent_f_name'] . ' ' . $profile['agent_l_name']); ?></h2>
                                            <p><?php echo $escape($profile['field_expertise']); ?></p>
                                        </div>
                                    </div>
                                    <a href="selected_agent_ideas.php?agent_profile_id=<?php echo rawurlencode($encodedId); ?>">Explore</a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No approved mentors found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <section id="footer"><p>© 2025 BizLaunchHub. All Rights Reserved.</p></section>
    <script src="../../public/assets/JS/siderbar.js"></script>
</body>
</html>
<?php $statement->close(); ?>