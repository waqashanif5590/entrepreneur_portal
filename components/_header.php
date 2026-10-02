 <section id="header">
     <div class="logo">
         <img src="../../public/assets/images/BZH7.png" alt="">
     </div>

     <div class="profile">

         <div class="profile_icon">
             <!-- if session is set then show profile image otherwise show default image -->
             <?php
                if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
                    require_once __DIR__ . '/../config/database.php';
                    $agent_account_id = (int)$_SESSION['id'];
                    $statement = $conn->prepare('SELECT profile_image FROM profiles WHERE agent_account_id = ? LIMIT 1');
                    $statement->bind_param('i', $agent_account_id);
                    $statement->execute();
                    $row = $statement->get_result()->fetch_assoc();
                    $statement->close();
                    if (!empty($row['profile_image'])) {
                        echo '<img src="../profiles/profile_image.php?agent_account_id=' . $agent_account_id . '" alt="Profile Image">';
                    } else {
                        echo '<img src="../../public/assets/images/profile.png" alt="Default Profile Image">';
                    }
                } else {
                    echo '<img src="../../public/assets/images/profile.png" alt="Default Profile Image">';
                }
                ?>
         </div>
         <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
                echo ' <h1>' .  $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] . '</h1><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i></a>';
            }; ?>
     </div>
 </section>