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
     <link rel="stylesheet" href="../CSS/all_agents.css">
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

             <!-- Status Filter -->
             <div class="filter_box">
                 <label>
                     <input type="radio" name="agent_status" checked>
                     <span>All</span>
                 </label>

                 <label>
                     <input type="radio" name="agent_status">
                     <span class="Approved">Approved</span>
                 </label>

                 <label>
                     <input type="radio" name="agent_status">
                     <span class="Pending">Pending</span>
                 </label>

                 <label>
                     <input type="radio" name="agent_status">
                     <span class="Blocked">Blocked</span>
                 </label>
             </div>
             <!-- Agents Table -->
             <div class="table_container">
                 <table>
                     <thead>
                         <tr>
                             <th>Profile</th>
                             <th>Full Name</th>
                             <th>Idea Title</th>
                             <th>Expertise</th>
                             <th>Status</th>
                             <th>Action</th>
                         </tr>
                     </thead>

                     <tbody>


                         <?php
                            $sql = "SELECT * FROM business_ideas 
              WHERE idea_title LIKE '%$query%' 
              OR idea_category LIKE '%$query%'
              OR problem_statement LIKE '%$query%'
              OR keywords LIKE '%$query%'";

                            $result = mysqli_query($conn, $sql);
                            if (mysqli_num_rows($result) == 0) {
                                echo '<tr>
                            <td colspan="6">No result found</td>
                        </tr>';
                            }
                            while ($row = mysqli_fetch_assoc($result)) {
                                $agent_id = $row["agent_profile_id"];
                                $business_idea_id = $row["idea_id"];
                                $sql2 = "SELECT * FROM `profiles` WHERE agent_account_id='$agent_id'";
                                $result2 = mysqli_query($conn, $sql2);
                                while ($row2 = mysqli_fetch_assoc($result2)) {
                                    $field_expertise = $row2["field_expertise"];
                                    $agent_f_name = $row2["agent_f_name"];
                                    $agent_l_name = $row2["agent_l_name"];
                                }
                                echo '<tr>
                            <td><img src="../uploads/profiles/' . $row['profile_image'] . '" class="table_profile"></td>
                            <td>' . $agent_f_name . ' ' . $agent_l_name . '</td>
                            <td><span>' . $row["idea_title"] . '</span></td>
                            <td>' . $field_expertise . '</td>
                            <td><span class="badge ' . $row["status"] . '">' . $row["status"] . '</span></td>
                            <td><a href="./idea_details_admin.php?agent_profile_id=' . base64_encode($agent_id) . '&business_idea_id=' . base64_encode($business_idea_id) . '">View</a></td>
                        </tr>';
                            }

                            ?>
                     </tbody>
                 </table>
             </div>

         </section>
     </div>
     <section id="footer">
         <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
     </section>

     <script src="../JS/siderbar.js"></script>
 </body>

 </html>