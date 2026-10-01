<?php
session_start();
$alert = '';
if (isset($_GET['type'])) {
    $entity_type = $_GET['type'];
}

// Generate CAPTCHA if not set or reload requested
if (!isset($_SESSION['captcha']) || isset($_GET['reload_captcha'])) {
    $_SESSION['captcha'] = generateCaptcha();
}

function generateCaptcha()
{
    $chars = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ1234567890';
    $captcha = '';
    for ($i = 0; $i < 6; $i++) {
        $captcha .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $captcha;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../database.php';
    $email = $_POST["email"];
    $password = $_POST["password"];
    $user_captcha = $_POST["verify_captcha"];

    // Verify CAPTCHA
    if ($user_captcha !== $_SESSION['captcha']) {
        $alert = "❌ Invalid CAPTCHA.";
    } else {
        $sql = "SELECT * FROM `accounts` WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $entity = $row["entity_type"];
                if ($entity != $entity_type) {
                    $alert = ' ❌ No ' . $entity_type . ' Exists for this email';
                } else if ($row['status'] === 'Blocked') {
                    $alert = "❌ Your account is blocked.";
                } else if (password_verify($password, $row['password'])) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['firstname'] = $row['first_name'];
                    $_SESSION['lastname'] = $row['last_name'];
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['email'] = $row['email'];

                    // Regenerate CAPTCHA after successful login
                    $_SESSION['captcha'] = generateCaptcha();

                    header('Location: ' . $entity_type . '_portal.php');
                    exit;
                } else {
                    $alert = "❌ Invalid password.";
                }
            }
        } else {
            $alert = "❌ No account found with that email.";
        }
    }

    // Regenerate CAPTCHA on failed attempt
    $_SESSION['captcha'] = generateCaptcha();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'partials/_header.php'; ?>
    <div id="container">

        <section id="main_content">
            <div class="form_container">
                <h1>Login to Entrepreneur Portal</h1>
                <form action="./login.php?type=<?php echo $entity_type; ?>" id="form" method="post">
                    <div class="field_set">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email">
                    </div>
                    <div class="field_set">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password">
                    </div>
                    <p class="account_alert"><?php echo $alert; ?></p>
                    <p>Don't have an account?<a href="./register.php">Register</a></p>
                    <div class="captcha">
                        <p id="random_captcha" class="random_captcha"><?php echo $_SESSION['captcha']; ?></p>
                        <a href="./login.php?type=<?php echo $entity_type; ?>&reload_captcha=1" id="reloader"><i class="fas fa-sync-alt"></i></a>
                        <input type="text" name="verify_captcha" id="verify_captcha" class="verify_captcha">
                    </div>
                    <button id="submit_btn">Submit</button>
                </form>
            </div>
        </section>

    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>
    <script>
        // CAPTCHA is now handled server-side
    </script>
</body>

</html>