<?php
session_start();
include '../database.php';

$query = "";
if (isset($_GET['q'])) {
    $query = mysqli_real_escape_string($conn, $_GET['q']);
}

// Search Ideas
$ideas_sql = "SELECT * FROM business_ideas 
              WHERE idea_title LIKE '%$query%' 
              OR idea_category LIKE '%$query%'
              OR problem_statement LIKE '%$query%'
              OR keywords LIKE '%$query%'";

$ideas_result = mysqli_query($conn, $ideas_sql);

// Search Mentors
$mentor_sql = "SELECT * FROM profiles
               WHERE agent_f_name LIKE '%$query%'
               OR agent_l_name LIKE '%$query%'
               OR field_expertise LIKE '%$query%'
               OR org_name LIKE '%$query%'";

$mentor_result = mysqli_query($conn, $mentor_sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Search</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/user_portal.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>

    <?php include 'partials/_header.php'; ?>
    <div id="container">
        <?php include 'partials/_sidebar.php'; ?>

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
                            $id = base64_encode($row["idea_id"]);
                            $agent = base64_encode($row["agent_profile_id"]);
                            echo "
        <div class='business_idea_card'>
            <h2>{$row['idea_title']}</h2>
            <p>" . substr($row['problem_statement'], 0, 150) . "...</p>
            <div class='idea_bottom_section'>
            <a href='idea_details_user.php?business_idea_id=$id&agent_profile_id=$agent'>
                View Idea
            </a>
            <span>" . $row["idea_category"] . "</span>
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
                            $id = base64_encode($row["agent_account_id"]);
                            echo "
        <div class='mentors_card'>
            <div class='mentor'>
                <div class='mentor_icon'>
                    <img src='../uploads/profiles/" . $row['profile_image'] . "' alt='Profile Image'>
                </div>
                <div class='mentor_profile'>
                    <h1>{$row['agent_f_name']} {$row['agent_l_name']}</h1>
                    <p>{$row['field_expertise']}</p>
                </div>
            </div>
            <a href='selected_agent_ideas.php?agent_profile_id=$id'>Explore</a>
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
    <script src="../JS/siderbar.js"></script>
</body>

</html>