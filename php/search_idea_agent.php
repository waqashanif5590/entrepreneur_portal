 <?php
    session_start();
    include '../database.php';

    $query = "";
    if (isset($_GET['q'])) {
        $query = mysqli_real_escape_string($conn, $_GET['q']);
    }
    ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <title>Search Idea</title>
     <link rel="stylesheet" href="../CSS/style.css">
     <link rel="stylesheet" href="../CSS/agent_portal.css">
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
             <!-- Search Box -->
             <div class="search_bar">
                 <form action="search_ideas.php" method="get">
                     <input type="text" name="q" id="search" placeholder="Search ideas by title or keywords...">
                     <button id="search_button"><i class="fas fa-search"></i></button>
                 </form>
             </div>
             <?php echo '<h1 class="search_keyword">Search results for " ' . $query . ' "'; ?>

             <?php
                if (isset($_GET['alert'])) {
                    $alert = $_GET['alert'];
                    echo '<p id="alert_message">' . $alert . '</p>';
                    unset($alert);
                }
                ?>
             <div class="dashboard" style="margin-top: 20px;">
                 <div class="card_container">
                     <?php
                        $sql = "SELECT * FROM business_ideas 
              WHERE idea_title LIKE '%$query%' 
              OR idea_category LIKE '%$query%'
              OR problem_statement LIKE '%$query%'
              OR keywords LIKE '%$query%'";

                        $result = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($result) == 0) {
                            echo '<div class="card">
                                        <p>No result found</p>
                                        </div>';
                        }
                        while ($row = mysqli_fetch_assoc($result)) {
                            $business_idea_id = base64_encode($row["idea_id"]);
                            $agent_profile_id = base64_encode($row["agent_profile_id"]);
                            $agent_id = $_SESSION['id'];
                            $sql2 = "SELECT * FROM `profiles` WHERE agent_account_id='$agent_id'";
                            $result2 = mysqli_query($conn, $sql2);
                            while ($row2 = mysqli_fetch_assoc($result2)) {
                                $field_expertise = $row2["field_expertise"];
                                $agent_name = $row2["agent_f_name"] . ' ' . $row2["agent_l_name"];
                            }
                            echo ' <div class="card">
                                        <h2>' . $row["idea_title"] . '</h2>
                                        <p>' . $row["problem_statement"] . '</p>
                                        <div class="buttons">
                                        <a href="./idea_details_agent.php?business_idea_id=' . $business_idea_id . '&agent_profile_id=' . $agent_profile_id . '" class="explore_btn">Explore</a>
                                        <a href="./edit_idea.php?business_idea_id=' . $business_idea_id . '&agent_profile_id=' . $agent_profile_id . '" class="edit_idea">Edit</a>
                                        <a href="./delete_idea.php?business_idea_id=' . $business_idea_id . '&agent_profile_id=' . $agent_profile_id . '" class="delete_idea">Delete</a>
                                        <p class="status status_' . $row["status"] . '">' . $row["status"] . '</p>
                                        </div>
                                        </div>';
                        }

                        ?>
                 </div>
             </div>
         </section>
     </div>
     <section id="footer">
         <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
     </section>

     <script src="../JS/siderbar.js"></script>
 </body>

 </html>