<?php
// update_profile.php

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

requireAccountRole($conn, ['agent']);
$csrfToken = csrfToken();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../auth/login_dashboard.php");
    exit;
} 

$agent_account_id = (int)$_SESSION['id'];
$alert = '';

// ====================== FETCH EXISTING PROFILE ======================
$profileStatement = $conn->prepare('SELECT * FROM profiles WHERE agent_account_id = ? LIMIT 1');
$profileStatement->bind_param('i', $agent_account_id);
$profileStatement->execute();
$profile = $profileStatement->get_result()->fetch_assoc();
$profileStatement->close();

if (!$profile) {
    $alert = "❌ No profile found. Please create your profile first.";
    // Optionally redirect to create_profile.php
    // header("Location: create_profile.php"); exit;
}

// ====================== HANDLE FORM SUBMISSION ======================
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    requirePostCsrfToken();

    $firstname     = (string)$_SESSION['firstname'];
    $lastname      = (string)$_SESSION['lastname'];
    $dob           = trim($_POST["dob"] ?? '');
    $country       = trim($_POST["country"] ?? '');
    $city          = trim($_POST["city"] ?? '');
    $contact       = trim($_POST["contact"] ?? '');
    $email         = (string)$_SESSION['email'];
    $field         = trim($_POST["field"] ?? '');
    $organization  = trim($_POST["organization"] ?? '');
    $experience    = filter_var($_POST["experience"] ?? 0, FILTER_VALIDATE_INT);
    $experience = $experience === false ? 0 : $experience;
    $bio           = trim($_POST["bio"] ?? '');
    $personal_web  = trim($_POST["personal_web"] ?? '');

    // Validation
    if ($country == 'none' || $field == 'none' || empty($dob) || empty($city) || empty($contact)) {
        $alert = "❌ Please fill all required fields";
    } else {

        $upload_dir = dirname(__DIR__, 2) . '/uploads/profiles/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $private_upload_dir = dirname(__DIR__, 2) . '/storage/private_profiles/';
        if (!is_dir($private_upload_dir) && !mkdir($private_upload_dir, 0750, true) && !is_dir($private_upload_dir)) {
            $alert = '❌ Unable to prepare secure document storage.';
        }

        $profile_image    = $profile['profile_image'];   // Keep old if not updated
        $cnic_path        = $profile['cnic'];
        $resume_path      = $profile['resume'];
        $certificate_path = $profile['certificate'];

        // Reuse the same uploadFile function
        function uploadFile($fileInputName, $allowedTypes, $upload_dir, $maxSizeMB = 2)
        {
            if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => "No new file uploaded"];
            }

            $file = $_FILES[$fileInputName];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileSize = $file['size'];

            if ($fileSize > $maxSizeMB * 1024 * 1024) {
                return ['success' => false, 'message' => "File too large. Max $maxSizeMB MB allowed."];
            }

            if (!in_array($fileExt, $allowedTypes)) {
                return ['success' => false, 'message' => "Invalid file type. Allowed: " . implode(', ', $allowedTypes)];
            }

            $newFileName = uniqid('profile_') . '_' . time() . '.' . $fileExt;
            $targetPath = $upload_dir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return ['success' => true, 'path' => $newFileName];
            } else {
                return ['success' => false, 'message' => "Failed to move uploaded file"];
            }
        }

        // 1. Profile Picture (Optional - only update if new file selected)
        if (!empty($_FILES['profile_pic']['name'])) {
            $imgResult = uploadFile('profile_pic', ['jpg', 'jpeg', 'png', 'gif'], $upload_dir, 2);
            if ($imgResult['success']) {
                $profile_image = $imgResult['path'];
                // Optional: Delete old image file here if you want
            } else {
                $alert = "❌ Profile Picture: " . $imgResult['message'];
            }
        }

        // 2. CNIC (Optional)
        if (empty($alert) && !empty($_FILES['cnic']['name'])) {
            $cnicResult = uploadFile('cnic', ['jpg', 'jpeg', 'png', 'gif'], $private_upload_dir, 2);
            if ($cnicResult['success']) {
                $cnic_path = $cnicResult['path'];
            } else {
                $alert = "❌ CNIC: " . $cnicResult['message'];
            }
        }

        // 3. Resume (Optional)
        if (empty($alert) && !empty($_FILES['resume']['name'])) {
            $resumeResult = uploadFile('resume', ['pdf', 'docx'], $private_upload_dir, 2);
            if ($resumeResult['success']) {
                $resume_path = $resumeResult['path'];
            } else {
                $alert = "❌ Resume: " . $resumeResult['message'];
            }
        }

        // 4. Experience Certificates (Optional)
        if (empty($alert) && !empty($_FILES['documents']['name'])) {
            $certResult = uploadFile('documents', ['pdf', 'docx'], $private_upload_dir, 2);
            if ($certResult['success']) {
                $certificate_path = $certResult['path'];
            } else {
                $alert = "❌ Certificate: " . $certResult['message'];
            }
        }

        // ====================== UPDATE DATABASE ======================
        if (empty($alert)) {
            $statement = $conn->prepare("UPDATE profiles SET agent_f_name = ?, agent_l_name = ?, dob = ?, country = ?, city = ?, contact = ?, agent_email = ?, field_expertise = ?, org_name = ?, experience = ?, agent_weblink = ?, agent_bio = ?, profile_image = ?, cnic = ?, resume = ?, certificate = ?, status = 'Pending' WHERE agent_account_id = ?");
            $statement->bind_param('sssssssssissssssi', $firstname, $lastname, $dob, $country, $city, $contact, $email, $field, $organization, $experience, $personal_web, $bio, $profile_image, $cnic_path, $resume_path, $certificate_path, $agent_account_id);
            $result = $statement->execute();
            $statement->close();

            if ($result) {
                $alert = "✅ Profile Updated Successfully!";
                header("Location: agent_portal.php?alert=" . urlencode($alert));
                exit;
            } else {
                http_response_code(500);
                $alert = "❌ Unable to update profile.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="../../public/assets/CSS/create_profile.css">
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../components/_header.php'; ?>

    <div id="container">
        <?php include __DIR__ . '/../../components/_sidebar.php'; ?>

        <section id="main_content">
            <h2>Update Your Profile</h2>
            <p>Make changes to your profile information below.</p>

            <?php if (!empty($alert)): ?>
                <div class="alert <?= strpos($alert, '✅') !== false ? 'success' : 'error' ?>">
                    <?= $alert ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" id="profile_form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="field_set">
                    <label for="firstname">First Name:</label>
                    <input type="text" id="firstname" name="firstname" required
                        value="<?= htmlspecialchars($profile['agent_f_name'] ?? $_SESSION['firstname']) ?>" readonly>
                </div>

                <div class="field_set">
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" required
                        value="<?= htmlspecialchars($profile['agent_l_name'] ?? $_SESSION['lastname']) ?>" readonly>
                </div>

                <div class="field_set">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" name="dob" id="dob"
                        value="<?= htmlspecialchars($profile['dob'] ?? '') ?>">
                </div>

                <div class="field_set">
                    <label for="country">Country:</label>
                    <select name="country" id="country">
                        <option value="none">-- Select --</option>
                        <option value="Afghanistan" <?= ($profile['country'] ?? '') == 'Afghanistan' ? 'selected' : '' ?>>Afghanistan</option>
                        <option value="Pakistan" <?= ($profile['country'] ?? '') == 'Pakistan' ? 'selected' : '' ?>>Pakistan</option>
                        <option value="Bangladesh" <?= ($profile['country'] ?? '') == 'Bangladesh' ? 'selected' : '' ?>>Bangladesh</option>
                        <option value="India" <?= ($profile['country'] ?? '') == 'India' ? 'selected' : '' ?>>India</option>
                    </select>
                </div>

                <div class="field_set">
                    <label for="city">City:</label>
                    <input type="text" name="city" id="city" placeholder="i.e Lahore"
                        value="<?= htmlspecialchars($profile['city'] ?? '') ?>">
                </div>

                <div class="field_set">
                    <label for="contact">Contact:</label>
                    <input name="contact" id="contact" placeholder="i.e +92 3451234567"
                        value="<?= htmlspecialchars($profile['contact'] ?? '') ?>">
                </div>

                <div class="field_set">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required
                        value="<?= htmlspecialchars($profile['agent_email'] ?? $_SESSION['email']) ?>" readonly>
                </div>

                <div class="field_set">
                    <label for="field">Field of Interest:</label>
                    <select name="field" id="field">
                        <option value="none">-- Select --</option>
                        <option value="Technology" <?= ($profile['field_expertise'] ?? '') == 'Technology' ? 'selected' : '' ?>>Technology</option>
                        <option value="Business" <?= ($profile['field_expertise'] ?? '') == 'Business' ? 'selected' : '' ?>>Business</option>
                        <!-- Add other options similarly -->
                        <option value="Education" <?= ($profile['field_expertise'] ?? '') == 'Education' ? 'selected' : '' ?>>Education</option>
                        <option value="Healthcare" <?= ($profile['field_expertise'] ?? '') == 'Healthcare' ? 'selected' : '' ?>>Healthcare</option>
                        <option value="Finance" <?= ($profile['field_expertise'] ?? '') == 'Finance' ? 'selected' : '' ?>>Finance</option>
                        <option value="Agriculture" <?= ($profile['field_expertise'] ?? '') == 'Agriculture' ? 'selected' : '' ?>>Agriculture</option>
                        <option value="Manufacturing" <?= ($profile['field_expertise'] ?? '') == 'Manufacturing' ? 'selected' : '' ?>>Manufacturing</option>
                        <option value="Energy" <?= ($profile['field_expertise'] ?? '') == 'Energy' ? 'selected' : '' ?>>Energy</option>
                        <option value="Transportation" <?= ($profile['field_expertise'] ?? '') == 'Transportation' ? 'selected' : '' ?>>Transportation</option>
                        <option value="Entertainment" <?= ($profile['field_expertise'] ?? '') == 'Entertainment' ? 'selected' : '' ?>>Entertainment</option>
                        <option value="Sports" <?= ($profile['field_expertise'] ?? '') == 'Sports' ? 'selected' : '' ?>>Sports</option>
                    </select>
                </div>

                <div class="field_set">
                    <label for="experience">Years of Experience:</label>
                    <input type="number" name="experience" id="experience" placeholder="i.e 5"
                        value="<?= htmlspecialchars($profile['experience'] ?? '') ?>">
                </div>

                <div class="field_set">
                    <label for="organization">Organization:</label>
                    <input type="text" name="organization" id="organization" placeholder="i.e XYZ Tech Solutions"
                        value="<?= htmlspecialchars($profile['org_name'] ?? '') ?>">
                </div>

                <div class="field_set">
                    <label for="bio">Bio:</label>
                    <textarea name="bio" id="bio" placeholder="Tell us about yourself..."><?= htmlspecialchars($profile['agent_bio'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label for="personal_web">Personal Website:</label>
                    <input type="url" name="personal_web" id="personal_web" placeholder="i.e https://www.johndoe.com"
                        value="<?= htmlspecialchars($profile['agent_weblink'] ?? '') ?>">
                </div>

                <!-- File Uploads - Show current files + option to change -->
                <div class="field_set">
                    <label>Current Profile Picture:</label><br>
                    <?php if (!empty($profile['profile_image'])): ?>
                        <img src="../profiles/profile_image.php?agent_account_id=<?= $agent_account_id ?>" width="120" style="border-radius:8px; margin-bottom:8px;"><br>
                    <?php endif; ?>
                    <label for="profile_pic">Change Profile Picture (Max 2MB)</label>
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/jpeg,image/png,image/gif">
                </div>

                <div class="field_set">
                    <label>Current CNIC:</label><br>
                    <?php if (!empty($profile['cnic'])): ?>
                        <a href="../profiles/download_document.php?agent_account_id=<?= $agent_account_id ?>&amp;document=cnic">View Current CNIC</a><br>
                    <?php endif; ?>
                    <label for="cnic">Change CNIC (Max 2MB)</label>
                    <input type="file" name="cnic" id="cnic" accept="image/jpeg,image/png,image/gif">
                </div>

                <div class="field_set">
                    <label>Current Resume:</label><br>
                    <?php if (!empty($profile['resume'])): ?>
                        <a href="../profiles/download_document.php?agent_account_id=<?= $agent_account_id ?>&amp;document=resume">View Current Resume</a><br>
                    <?php endif; ?>
                    <label for="resume">Change Resume (Max 2MB)</label>
                    <input type="file" name="resume" id="resume" accept=".pdf,.docx">
                </div>

                <div class="field_set">
                    <label>Current Experience Certificate:</label><br>
                    <?php if (!empty($profile['certificate'])): ?>
                        <a href="../profiles/download_document.php?agent_account_id=<?= $agent_account_id ?>&amp;document=certificate">View Current Certificate</a><br>
                    <?php endif; ?>
                    <label for="documents">Change Experience Certificate (Optional)</label>
                    <input type="file" name="documents" id="documents" accept=".pdf,.docx">
                </div>

                <div class="button_group">
                    <a href="./agent_portal.php" id="reset_btn">Back</a>
                    <button type="submit" id="submit_btn">Update Profile</button>
                </div>
            </form>
        </section>
    </div>

    <section id="footer">
        <p>© 2025 BizLaunchHub. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>