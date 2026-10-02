<?php
$alert = '';
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    require_once __DIR__ . '/../../config/database.php';

    $firstname = trim($_POST["first_name"] ?? '');
    $lastname = trim($_POST["last_name"] ?? '');
    $entity_type = $_POST["entity_type"] ?? '';
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirm_pass = $_POST["confirm_password"] ?? '';

    $validEntityType = in_array($entity_type, ['user', 'agent'], true);
    if (!$validEntityType) {
        $alert = '❌ Please select a valid account type.';
    } else {
        $checkStatement = $conn->prepare('SELECT id FROM accounts WHERE email = ? LIMIT 1');
        $checkStatement->bind_param('s', $email);
        $checkStatement->execute();
        $emailExists = $checkStatement->get_result()->num_rows > 0;
        $checkStatement->close();
    }
    if ($validEntityType && $emailExists) {
        $alert = "❌ Email or Username already exists.";
    } elseif ($validEntityType) {
        if ($password !== '' && $password === $confirm_pass) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insertStatement = $conn->prepare('INSERT INTO accounts (first_name, last_name, entity_type, email, password) VALUES (?, ?, ?, ?, ?)');
            $insertStatement->bind_param('sssss', $firstname, $lastname, $entity_type, $email, $hash);
            if ($insertStatement->execute()) {
                $alert = "✅ Account created successfully";
                header('Location: login.php?type=' . $entity_type);
                exit;
            } else {
                $alert = '❌ Unable to create the account.';
            }
            $insertStatement->close();
        } else {
            $alert = '❌ Password do not match';
        }
    }
}

// inserting random profiles

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/register.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../components/_header.php'; ?>
    <div id="container">

        <section id="main_content">
            <div class="form_container">
                <h1>Register to Entrepreneur Portal</h1>
                <form action="./register.php" id="form" method="post">
                    <div class="field_set">
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" required>
                    </div>
                    <div class="field_set">
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" required>
                    </div>
                    <div class="field_set">
                        <label for="entity_type">Choose Entity Type</label>
                        <select name="entity_type" id="entity_type" required>
                            <option value="select">-- Select type --</option>
                            <option value="agent">Agent</option>
                            <option value="user">User</option>
                        </select>
                        <ul>
                            <li> Agent: Create and manage AI agents, set up profiles, and offer services.</li>
                            <li> User: Browse agent profiles, interact with agents, and access AI services.</li>
                        </ul>
                    </div>
                    <div class="field_set">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="field_set">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <div class="field_set">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>
                    <p class="account_alert"><?php echo $alert; ?></p>
                    <p>Already have an account?<a href="./login_dashboard.php">Login</a></p>
                    <div class="confirm_registration">
                        <input type="checkbox" name="confirm" id="confirm" required>
                        <label for="confirm">I accept the <a href="../home/privacyPolicy.php">Privacy Policy</a> and the <a href="../home/terms&amp;condition.php">Terms of Service</a></label>
                    </div>
                    <button id="submit_btn">Submit</button>
                </form>
            </div>
        </section> 

    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>
</body>

</html>