<section id="sidebar" class="sidebar">
    <div class="navbar_title">
        <h1>BizLaunchHub</h1>
        <div class="hamburger_full">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>
    </div>

    <nav id="navbar">
        <?php
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {

            $logged_id = $_SESSION['id'];

            $accountStatement = $conn->prepare('SELECT entity_type FROM accounts WHERE id = ? LIMIT 1');
            $accountStatement->bind_param('i', $logged_id);
            $accountStatement->execute();
            $row = $accountStatement->get_result()->fetch_assoc();
            $accountStatement->close();

            // Count unread messages
            $unreadStatement = $conn->prepare('SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND is_read = 0');
            $unreadStatement->bind_param('i', $logged_id);
            $unreadStatement->execute();
            $unread_row = $unreadStatement->get_result()->fetch_assoc();
            $unreadStatement->close();
            $unread_count = $unread_row['unread_count'];

            // Add class if unread exists
            $dmpClass = ($unread_count > 0) ? 'alert' : '';

            if ($row["entity_type"] === 'user') {

                echo '
                <a href="../entrepreneur/user_portal.php"><i class="fas fa-home"></i><span class="nav_links">Home</span></a>
                <a href="../community/forum_list_shared.php"><i class="fas fa-comments"></i><span class="nav_links">Join Forum</span></a>
                <a href="../profiles/agent_profiles.php"><i class="fas fa-search"></i><span class="nav_links">Search Profiles</span></a>
                <a href="../entrepreneur/templates_list.php"><i class="fas fa-file-alt"></i><span class="nav_links">Search Templates</span></a>

                <a href="../messenger/messanger.php" class="dmp-link ' . $dmpClass . '">
                    <i class="fas fa-comments"></i>
                    <span class="nav_links">DMP</span>';

                if ($unread_count > 0) {
                    echo '<span class="badge">' . $unread_count . '</span>';
                }

                echo '</a>
                <a href="../home/about.php"><i class="fas fa-info-circle"></i><span class="nav_links">About</span></a>';
            } else if ($row["entity_type"] === 'agent') {

                $agentAccountId = (int)$_SESSION['id'];

                $checkProfileStatement = $conn->prepare('SELECT status FROM profiles WHERE agent_account_id = ? LIMIT 1');
                $checkProfileStatement->bind_param('i', $logged_id);
                $checkProfileStatement->execute();
                $check_profile_row = $checkProfileStatement->get_result()->fetch_assoc();
                $checkProfileStatement->close();
                if (!$check_profile_row) {
                    $url = "../mentor/create_profile.php";
                } else if ($check_profile_row['status'] === 'Pending') {
                    $alert = "Your profile is pending for admin approval. Please wait for admin response";
                    $url = "../mentor/agent_portal.php?alert=$alert";
                } else {
                    $url = "../mentor/create_forum.php";
                }
                echo '
                <a href="../mentor/agent_portal.php"><i class="fas fa-home"></i><span class="nav_links">Home</span></a>
                <a href="../profiles/selected_agent.php?agent_account_id=' . $agentAccountId . '"><i class="fas fa-user-tie"></i><span class="nav_links">My Profile</span></a>
                <a href="../profiles/selected_agent_ideas.php?agent_profile_id=' . $agentAccountId . '"><i class="fas fa-layer-group"></i><span class="nav_links">My domains</span></a>
                <a href="' . $url . '"><i class="fas fa-plus"></i><span class="nav_links">Create Forum</span></a>
                <a href="../community/forum_list_shared.php"><i class="fas fa-search"></i><span class="nav_links">Explore Forum</span></a>

                <a href="../messenger/messanger.php" class="dmp-link ' . $dmpClass . '">
                    <i class="fas fa-comments"></i>
                    <span class="nav_links">DMP</span>';

                if ($unread_count > 0) {
                    echo '<span class="badge">' . $unread_count . '</span>';
                }

                echo '</a>
                <a href="../home/about.php"><i class="fas fa-info-circle"></i><span class="nav_links">About</span></a>';
            } else if ($row["entity_type"] === 'admin') {

                echo '
                <a href="../admin/admin_portal.php"><i class="fas fa-home"></i><span class="nav_links">Home</span></a>
                <a href="../profiles/agent_profiles.php"><i class="fas fa-user-tie"></i><span class="nav_links">Agents</span></a>
                <a href="../admin/users_profile_list.php"><i class="fas fa-users"></i><span class="nav_links">Users</span></a>
                <a href="../ideas/ideas_list.php"><i class="fas fa-layer-group"></i><span class="nav_links">Business Domains</span></a>
                <a href="../community/forum_list_shared.php"><i class="fas fa-comments"></i><span class="nav_links">Forums</span></a>
                <a href="../home/about.php"><i class="fas fa-info-circle"></i><span class="nav_links">About</span></a>';
            }
        }
        ?>
    </nav>
    <a href="../auth/logout.php" id="login-btn"><span>Logout</span><i class="fas fa-sign-out-alt"></i></a>
</section>