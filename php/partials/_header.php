 <section id="header">
     <div class="logo">
         <img src="../images/BZH7.png" alt="">
     </div>

     <div class="profile">

         <div class="profile_icon">
             <!-- if session is set then show profile image otherwise show default image -->
             <?php
                if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
                    include '../database.php';
                    $agent_account_id = $_SESSION['id'];
                    $sql = "SELECT profile_image FROM profiles WHERE agent_account_id='$agent_account_id'";
                    $result = mysqli_query($conn, $sql);
                    if (!$result) {
                        echo "Error: " . mysqli_error($conn);
                    }
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        if (!empty($row['profile_image'])) {
                            echo '<img src="../uploads/profiles/' . $row['profile_image'] . '" alt="Profile Image">';
                        } else {
                            echo '<img src="../images/profile.png" alt="Default Profile Image">';
                        }
                    } else {
                        echo '<img src="../images/profile.png" alt="Default Profile Image">';
                    }
                } else {
                    echo '<img src="../images/profile.png" alt="Default Profile Image">';
                }
                ?>
         </div>
         <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
                echo ' <h1>' .  $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] . '</h1><a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>';
            }; ?>
     </div>
 </section>