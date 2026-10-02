 <?php
    session_start();
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../config/security.php';
    $viewerRole = requireAccountRole($conn, ['agent']);
    $viewerId = (int)$_SESSION['id'];
    $csrfToken = htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8');

    $query = $_GET['q'] ?? '';
    if (!is_string($query)) {
        http_response_code(400);
        exit('Invalid search query.');
    }
    $pattern = '%' . $query . '%';
    $statement = $conn->prepare('SELECT * FROM business_ideas WHERE user_id = ? AND (idea_title LIKE ? OR idea_category LIKE ? OR problem_statement LIKE ? OR keywords LIKE ?)');
    $statement->bind_param('issss', $viewerId, $pattern, $pattern, $pattern, $pattern);
    $statement->execute();
    $ideas = $statement->get_result();
    $escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <title>Search Idea</title>
     <link rel="stylesheet" href="../../public/assets/CSS/style.css">
     <link rel="stylesheet" href="../../public/assets/CSS/agent_portal.css">
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
             <!-- Search Box -->
             <div class="search_bar">
                 <form action="search_ideas.php" method="get">
                     <input type="text" name="q" id="search" placeholder="Search ideas by title or keywords...">
                     <button id="search_button"><i class="fas fa-search"></i></button>
                 </form>
             </div>
             <?php echo '<h1 class="search_keyword">Search results for " ' . $escape($query) . ' "'; ?>

             <?php
                if (isset($_GET['alert'])) {
                    $alert = $_GET['alert'];
                    echo '<p id="alert_message">' . $escape($alert) . '</p>';
                    unset($alert);
                }
                ?>
             <div class="dashboard" style="margin-top: 20px;">
                 <div class="card_container">
                     <?php
                            if ($ideas->num_rows === 0) {
                            echo '<div class="card">
                                        <p>No result found</p>
                                        </div>';
                        }
                        while ($row = $ideas->fetch_assoc()) {
                            $business_idea_id = (int)$row["id"];
                            $agent_profile_id = (int)$row["user_id"];
                            echo ' <div class="card">
                                        <h2>' . $escape($row["idea_title"]) . '</h2>
                                        <p>' . $escape($row["problem_statement"]) . '</p>
                                        <div class="buttons">
                                        <a href="../ideas/idea_details.php?business_idea_id=' . $business_idea_id . '" class="explore_btn">Explore</a>
                                        <a href="../mentor/update_idea.php?business_idea_id=' . $business_idea_id . '" class="edit_idea">Edit</a>
                                        <form action="../admin/delete_idea.php" method="post"><input type="hidden" name="csrf_token" value="' . $csrfToken . '"><input type="hidden" name="business_idea_id" value="' . $business_idea_id . '"><button class="delete_idea" type="submit">Delete</button></form>
                                        <p class="status status_' . $escape($row["status"]) . '">' . $escape($row["status"]) . '</p>
                                        </div>
                                        </div>';
                        }
                        $statement->close();
                        ?>
                 </div>
             </div>
         </section>
     </div>
     <section id="footer">
         <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
     </section>

     <script src="../../public/assets/JS/siderbar.js"></script>
 </body>

 </html>