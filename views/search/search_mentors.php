 <?php
    session_start();
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../config/security.php';
    $role = requireAccountRole($conn, ['admin', 'user']);

    $query = $_GET['q'] ?? '';
    if (!is_string($query)) {
        http_response_code(400);
        exit('Invalid search query.');
    }
    $pattern = '%' . $query . '%';
    $statement = $role === 'admin'
        ? $conn->prepare('SELECT * FROM profiles WHERE agent_f_name LIKE ? OR agent_l_name LIKE ? OR agent_email LIKE ? OR field_expertise LIKE ?')
        : $conn->prepare("SELECT profiles.* FROM profiles JOIN accounts ON accounts.id = profiles.agent_account_id WHERE profiles.status = 'Approved' AND accounts.entity_type = 'agent' AND accounts.status = 'Active' AND (profiles.agent_f_name LIKE ? OR profiles.agent_l_name LIKE ? OR profiles.agent_email LIKE ? OR profiles.field_expertise LIKE ?)");
    $statement->bind_param('ssss', $pattern, $pattern, $pattern, $pattern);
    $statement->execute();
    $profiles = $statement->get_result();
    $escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <title>Search Mentor</title>
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
                 <form action="search_mentors.php" method="get">
                     <input type="text" name="q" id="search" placeholder="Search mentors by name, email or expertise...">
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
             </div> -->

             <!-- Agents Table -->
             <div class="table_container">
                 <table>
                     <thead>
                         <tr>
                             <th>Profile</th>
                             <th>Full Name</th>
                             <th>Email</th>
                             <th>Status</th>
                             <th>Expertise</th>
                             <th>Action</th>
                         </tr>
                     </thead>

                     <tbody>


                         <?php
                            if ($profiles->num_rows === 0) {
                                echo '<tr>
                            <td colspan="6">No result found</td>
                        </tr>';
                            }
                            while ($row = $profiles->fetch_assoc()) {
                                $agent_account_id = (int)$row["agent_account_id"];
                                echo '<tr>
                                        <td><img src="../../views/profiles/profile_image.php?agent_account_id=' . (int)$row['agent_account_id'] . '" class="table_profile"></td>
                                        <td>' . $escape($row["agent_f_name"] . ' ' . $row["agent_l_name"]) . '</td>
                                        <td>' . $escape($row["agent_email"]) . '</td>
                                        <td><span class="badge ' . $escape($row["status"]) . '">' . $escape($row["status"]) . '</span></td>
                                        <td>' . $escape($row["field_expertise"]) . '</td>
                                        <td><a href="../profiles/selected_agent.php?agent_account_id=' . $agent_account_id . '">View</a></td>
                                    </tr>';
                            }

                            $statement->close();
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