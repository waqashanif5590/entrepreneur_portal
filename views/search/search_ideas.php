 <?php
    session_start();
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../config/security.php';

    $query = $_GET['q'] ?? '';
    if (!is_string($query)) {
        http_response_code(400);
        exit('Invalid search query.');
    }
    $viewerRole = '';
    $viewerId = (int)($_SESSION['id'] ?? 0);
    if (!empty($_SESSION['loggedin']) && $viewerId > 0) {
        $viewerRole = requireAccountRole($conn, ['admin', 'agent', 'user']);
    }
    $pattern = '%' . $query . '%';
    $where = "status = 'Approved' AND visibility = 'Public' AND (idea_title LIKE ? OR idea_category LIKE ? OR problem_statement LIKE ? OR keywords LIKE ?)";
    if ($viewerRole === 'admin') {
        $where = '(idea_title LIKE ? OR idea_category LIKE ? OR problem_statement LIKE ? OR keywords LIKE ?)';
    } elseif ($viewerRole === 'agent') {
        $where = "((status = 'Approved' AND visibility IN ('Public', 'Mentors Only')) OR user_id = ?) AND (idea_title LIKE ? OR idea_category LIKE ? OR problem_statement LIKE ? OR keywords LIKE ?)";
    }
    $escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <title>Search Idea</title>
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
                            $statement = $conn->prepare("SELECT * FROM business_ideas WHERE $where");
                            if ($viewerRole === 'agent') {
                                $statement->bind_param('issss', $viewerId, $pattern, $pattern, $pattern, $pattern);
                            } else {
                                $statement->bind_param('ssss', $pattern, $pattern, $pattern, $pattern);
                            }
                            $statement->execute();
                            $result = $statement->get_result();
                            if ($result->num_rows === 0) {
                                echo '<tr>
                            <td colspan="6">No result found</td>
                        </tr>';
                            }
                            while ($row = $result->fetch_assoc()) {
                                $agent_id = (int)$row["user_id"];
                                $business_idea_id = (int)$row["id"];
                                $profileStatement = $conn->prepare('SELECT field_expertise, agent_f_name, agent_l_name FROM profiles WHERE agent_account_id = ? LIMIT 1');
                                $profileStatement->bind_param('i', $agent_id);
                                $profileStatement->execute();
                                $row2 = $profileStatement->get_result()->fetch_assoc() ?? [];
                                $profileStatement->close();
                                $field_expertise = $row2["field_expertise"] ?? '';
                                $agent_f_name = $row2["agent_f_name"] ?? '';
                                $agent_l_name = $row2["agent_l_name"] ?? '';
                                echo '<tr>
                            <td><img src="../profiles/profile_image.php?agent_account_id=' . $agent_id . '" class="table_profile"></td>
                            <td>' . $escape($agent_f_name . ' ' . $agent_l_name) . '</td>
                            <td><span>' . $escape($row["idea_title"]) . '</span></td>
                            <td>' . $escape($field_expertise) . '</td>
                            <td><span class="badge ' . $escape($row["status"]) . '">' . $escape($row["status"]) . '</span></td>
                            <td><a href="../ideas/idea_details.php?business_idea_id=' . $business_idea_id . '">View</a></td>
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