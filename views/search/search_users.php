 <?php
    session_start();
    require_once __DIR__ . '/../../config/database.php';

    $query = "";
    if (isset($_GET['q'])) {
        $query = mysqli_real_escape_string($conn, $_GET['q']);
    }
    ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <title>Search User</title>
     <link rel="stylesheet" href="../../public/assets/CSS/style.css">
     <link rel="stylesheet" href="../../public/assets/CSS/all_agents.css">
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
                 <form action="search_users.php" method="get">
                     <input type="text" name="q" id="search" placeholder="Search users by name or email...">
                     <button id="search_button"><i class="fas fa-search"></i></button>
                 </form>
             </div>

             <!-- Status Filter -->
             <!-- <div class="filter_box">
                 <label>
                     <input type="radio" name="agent_status" checked>
                     <span>All</span>
                 </label>

                 <label>
                     <input type="radio" name="agent_status">
                     <span class="Approved">Active</span>
                 </label>

                 <label>
                     <input type="radio" name="agent_status">
                     <span class="Blocked">Blocked</span>
                 </label>
             </div> -->

             <?php
                if (isset($_GET['alert'])) {
                    $alert = $_GET['alert'];
                    echo '<p id="alert_message">' . $alert . '</p>';
                    unset($alert);
                }
                ?>

                <!-- Users table -->
             <div class="table_container">
                 <table>
                     <thead>
                         <tr>
                             <th>Profile</th>
                             <th>Full Name</th>
                             <th>Email</th>
                             <th>Status</th>
                             <th>Action</th>
                         </tr>
                     </thead>

                     <tbody>
                         <?php
                            $sql = "SELECT * FROM accounts 
              WHERE (first_name LIKE '%$query%' 
              OR last_name LIKE '%$query%'
              OR email LIKE '%$query%')
              AND (entity_type='user')";

                            $result = mysqli_query($conn, $sql);
                            if (mysqli_num_rows($result) == 0) {
                                echo '<tr>
                            <td colspan="6">No result found</td>
                        </tr>';
                            } else {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo '<tr>
                                        <td><img src="../../public/assets/images/profile.png" class="table_profile"></td>
                                        <td>' . $row["first_name"] . ' ' . $row["last_name"] . '</td>
                                        <td>' . $row["email"] . '</td>
                                        <td><span class="badge ' . ($row["status"] == "Blocked" ? "Blocked" : "Approved") . '">' . $row["status"] . '</span></td>
                                        <td><a href="../admin/block_user.php?id=' . $row["id"] . '">' . ($row["status"] == "Blocked" ? "Unblock" : "Block") . '</a></td>
                                    </tr>';
                                }
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

     <script src="../../public/assets/JS/siderbar.js"></script>
 </body>

 </html>