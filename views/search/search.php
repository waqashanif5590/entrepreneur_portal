<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$query = $_GET['q'] ?? '';
if (!is_string($query)) {
    http_response_code(400);
    exit('Invalid search query.');
}
$pattern = '%' . $query . '%';

$ideasStatement = $conn->prepare("SELECT * FROM business_ideas WHERE status = 'Approved' AND visibility = 'Public' AND (idea_title LIKE ? OR idea_category LIKE ? OR problem_statement LIKE ? OR keywords LIKE ?)");
$ideasStatement->bind_param('ssss', $pattern, $pattern, $pattern, $pattern);
$ideasStatement->execute();
$ideas_result = $ideasStatement->get_result();

$mentorStatement = $conn->prepare("SELECT profiles.* FROM profiles JOIN accounts ON accounts.id = profiles.agent_account_id WHERE profiles.status = 'Approved' AND accounts.entity_type = 'agent' AND accounts.status = 'Active' AND (profiles.agent_f_name LIKE ? OR profiles.agent_l_name LIKE ? OR profiles.field_expertise LIKE ? OR profiles.org_name LIKE ?)");
$mentorStatement->bind_param('ssss', $pattern, $pattern, $pattern, $pattern);
$mentorStatement->execute();
$mentor_result = $mentorStatement->get_result();
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Search</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
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
            <div class="search_bar">
                <form action="./search.php" method="get">
                    <input type="text" name="q" id="search" placeholder="Search mentors, ideas, templates, resources...">
                    <button id="search_button"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <h1>Search Results for: "<?php echo htmlspecialchars($query); ?>"</h1>
            <div class="business_ideas">
                <h1>Matching Business Ideas</h1>

                <div class="business_card_container">
                    <?php
                    if (mysqli_num_rows($ideas_result) > 0) {
                        while ($row = mysqli_fetch_assoc($ideas_result)) {
                            $id = (int)$row["id"];
                            $agent = (int)$row["user_id"];
                            echo "
        <div class='business_idea_card'>
            <h2>" . $escape($row['idea_title']) . "</h2>
            <p>" . $escape(substr($row['problem_statement'], 0, 150)) . "...</p>
            <div class='idea_bottom_section'>
            <a href='../ideas/idea_details.php?business_idea_id=$id&agent_profile_id=$agent'>
                View Idea
            </a>
            <span>" . $escape($row["idea_category"]) . "</span>
             </div>
        </div>";
                        }
                    } else {
                        echo "<p>No matching ideas found.</p>";
                    }
                    ?>
                </div>
            </div>

            <div class="mentors_recommendations">
                <h1>Matching Mentors</h1>

                <div class="mentors_card_container">
                    <?php
                    if (mysqli_num_rows($mentor_result) > 0) {
                        while ($row = mysqli_fetch_assoc($mentor_result)) {
                            $id = (int)$row["agent_account_id"];
                            echo "
        <div class='mentors_card'>
            <div class='mentor'>
                <div class='mentor_icon'>
                    <img src='../profiles/profile_image.php?agent_account_id=" . (int)$row['agent_account_id'] . "' alt='Profile Image'>
                </div>
                <div class='mentor_profile'>
                    <h1>" . $escape($row['agent_f_name'] . ' ' . $row['agent_l_name']) . "</h1>
                    <p>" . $escape($row['field_expertise']) . "</p>
                </div>
            </div>
            <a href='../profiles/selected_agent_ideas.php?agent_profile_id=$id'>Explore</a>
        </div>";
                        }
                    } else {
                        echo "<p>No matching mentors found.</p>";
                    }
                    ?>
                </div>
            </div>

        </section>
    </div>
    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>