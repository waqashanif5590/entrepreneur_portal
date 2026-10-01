<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <link rel="stylesheet" href="../CSS/login_dashboard.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'partials/_header.php'; ?>
    <div id="container">
        <section id="main_content">
            <h1>Choose your account login type</h1>
            <div class="entity_types_container">
                <a href="./login.php?type=admin" class="entity_box" id="admin_portal">
                    <div class="entity_image">
                        <img src="../images/profile.png" alt="">
                    </div>
                    <h1>Admin Login</h1>
                </a>
                <a href="./login.php?type=user" class="entity_box" id="user_portal">
                    <div class="entity_image">
                        <img src="../images/profile.png" alt="">
                    </div>
                    <h1>User Login</h1>
                </a>
                <a href="./login.php?type=agent" class="entity_box" id="agent_portal">
                    <div class="entity_image">
                        <img src="../images/profile.png" alt="">
                    </div>
                    <h1>Agent Login</h1>
                </a>
            </div>
            <div class="new_account">
                <p>Don't have an account?</p>
                <a href="./register.php">Register to Portal</a>
            </div>
        </section>
    </div>
    <section id="footer">
        <p>© 2025 BizLaunchHub. All Rights Reserved.</p>
    </section>
</body>

</html>