<?php
// create_profile.php 

session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../auth/login_dashboard.php");
    exit;
}

$oldInput = $_SESSION['profile_form_old'] ?? [];
$alert = $_SESSION['profile_form_alert'] ?? '';
unset($_SESSION['profile_form_old'], $_SESSION['profile_form_alert']);
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$isSelected = static fn(string $key, string $value): string => (($oldInput[$key] ?? '') === $value) ? ' selected' : '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $alert = '';

    $firstname     = mysqli_real_escape_string($conn, $_POST["firstname"]);
    $lastname      = mysqli_real_escape_string($conn, $_POST["lastname"]);
    $dob           = mysqli_real_escape_string($conn, $_POST["dob"]);
    $country       = mysqli_real_escape_string($conn, $_POST["country"]);
    $city          = mysqli_real_escape_string($conn, $_POST["city"]);
    $contact       = mysqli_real_escape_string($conn, $_POST["contact"]);
    $email         = mysqli_real_escape_string($conn, $_POST["email"]);
    $field         = mysqli_real_escape_string($conn, $_POST["field"]);
    $organization  = mysqli_real_escape_string($conn, $_POST["organization"]);
    $experience    = (int)$_POST["experience"];
    $bio           = mysqli_real_escape_string($conn, $_POST["bio"]);
    $personal_web  = mysqli_real_escape_string($conn, $_POST["personal_web"]);
    $agent_account_id = $_SESSION['id'];

    // Validation
    if ($country == 'none' || $field == 'none' || empty($dob) || empty($city) || empty($contact)) {
        $alert = "❌ Please fill all required fields";
    } else {
        // ====================== FILE UPLOAD HANDLING ======================

        $upload_dir = dirname(__DIR__, 2) . '/uploads/profiles/';

        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $profile_image = '';
        $resume_path   = '';
        $certificate_path = '';

        // Function to handle file upload
        function uploadFile($fileInputName, $allowedTypes, $upload_dir, $maxSizeMB = 2)
        {
            if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => "File $fileInputName upload error"];
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

            // Generate unique filename
            $newFileName = uniqid('profile_') . '_' . time() . '.' . $fileExt;
            $targetPath = $upload_dir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return ['success' => true, 'path' => $newFileName];
            } else {
                return ['success' => false, 'message' => "Failed to move uploaded file"];
            }
        }

        // 1. Profile Picture (Image only)
        $imgResult = uploadFile('profile_pic', ['jpg', 'jpeg', 'png', 'gif'], $upload_dir, 2);
        if ($imgResult['success']) {
            $profile_image = $imgResult['path'];
        } else {
            $alert = "❌ " . $imgResult['message'];
        }

        // 2. Resume (PDF or DOCX)
        if (empty($alert)) {
            $resumeResult = uploadFile('resume', ['pdf', 'docx'], $upload_dir, 2);
            if ($resumeResult['success']) {
                $resume_path = $resumeResult['path'];
            } else {
                $alert = "❌ Resume: " . $resumeResult['message'];
            }
        }

        // 3. Experience Certificates (Optional)
        if (empty($alert) && !empty($_FILES['documents']['name'])) {
            $certResult = uploadFile('documents', ['pdf', 'docx'], $upload_dir, 2);
            if ($certResult['success']) {
                $certificate_path = $certResult['path'];
            } else {
                $alert = "❌ Certificate: " . $certResult['message'];
            }
        }

        // ====================== INSERT INTO DATABASE ======================
        if (empty($alert)) {
            $sql = "INSERT INTO `profiles` 
                    (agent_f_name, agent_l_name, dob, country, city, contact, agent_email, 
                     field_expertise, org_name, experience, agent_weblink, agent_bio, 
                     agent_account_id, profile_image, resume, certificate, status) 
                    VALUES 
                    ('$firstname', '$lastname', '$dob', '$country', '$city', '$contact', '$email', 
                     '$field', '$organization', $experience, '$personal_web', '$bio', 
                     $agent_account_id, '$profile_image', '$resume_path', 
                     '$certificate_path', 'Pending')";

            $result = mysqli_query($conn, $sql);

            if ($result) {
                $alert = "✅ Profile Created Successfully!";
                header("Location: agent_portal.php?alert=" . urlencode($alert));
                exit;
            } else {
                $alert = "❌ Database Error: " . mysqli_error($conn);
            }
        }
    }

    if (!empty($alert)) {
        $oldInput = [];
        foreach (['dob', 'country', 'city', 'contact', 'field', 'experience', 'organization', 'bio', 'personal_web'] as $fieldName) {
            if (isset($_POST[$fieldName]) && is_scalar($_POST[$fieldName])) {
                $oldInput[$fieldName] = (string)$_POST[$fieldName];
            }
        }
        $_SESSION['profile_form_old'] = $oldInput;
        $_SESSION['profile_form_alert'] = $alert;
        header('Location: create_profile.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Profile</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <link rel="stylesheet" href="../../public/assets/CSS/create_profile.css">
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap"
        rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../components/_header.php'; ?>
    <div id="container">
        <?php include __DIR__ . '/../../components/_sidebar.php'; ?>
        <section id="main_content">
            <h2>Create Your Profile</h2>
            <p>To get started, please create your profile by filling out the form below.
                This will help us understand your background and connect you with the right resources.</p>

            <?php if (!empty($alert)): ?>
                <div class="alert <?= strpos($alert, '✅') !== false ? 'success' : 'error' ?>">
                    <?= $alert ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" id="profile_form" enctype="multipart/form-data">
                <div class="field_set">
                    <label for="firstname">First Name:</label>
                    <input type="text" id="firstname" name="firstname" required
                        value="<?php echo $escape($_SESSION['firstname']); ?>" readonly>
                </div>
                <div class="field_set">
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" required
                        value="<?php echo $escape($_SESSION['lastname']); ?>" readonly>
                </div>
                <div class="field_set">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" name="dob" id="dob" value="<?= $escape($oldInput['dob'] ?? '') ?>">
                </div>
                <div class="field_set">
                    <label for="country">Country:</label>
                    <select name="country" id="country">
                        <option value="none"<?= $isSelected('country', 'none') ?>>-- Select --</option>
                        <!-- <option value="Afghanistan">Afghanistan</option>
                        <option value="Pakistan">Pakistan</option>
                        <option value="Bangladesh">Bagladesh</option>
                        <option value="India">India</option> -->
                    </select>
                </div>
                <div class="field_set">
                    <label for="city">City:</label>
                    <input type="text" name="city" id="city" placeholder="i.e Lahore" value="<?= $escape($oldInput['city'] ?? '') ?>">
                </div>
                <div class="field_set">
                    <label for="contact">Contact:</label>
                    <input name="contact" id="contact" placeholder="i.e +92 3451234567" value="<?= $escape($oldInput['contact'] ?? '') ?>">
                </div>
                <div class="field_set">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required value="<?php echo $escape($_SESSION['email']); ?>"
                        readonly>
                </div>
                <div class="field_set">
                    <label for="field">Field of Interest:</label>
                    <select name="field" id="field">
                        <option value="none"<?= $isSelected('field', 'none') ?>>-- Select --</option>
                        <option value="Technology"<?= $isSelected('field', 'Technology') ?>>Technology</option>
                        <option value="Business"<?= $isSelected('field', 'Business') ?>>Business</option>
                        <option value="Education"<?= $isSelected('field', 'Education') ?>>Education</option>
                        <option value="Healthcare"<?= $isSelected('field', 'Healthcare') ?>>Healthcare</option>
                        <option value="Finance"<?= $isSelected('field', 'Finance') ?>>Finance</option>
                        <option value="Agriculture"<?= $isSelected('field', 'Agriculture') ?>>Agriculture</option>
                        <option value="Manufacturing"<?= $isSelected('field', 'Manufacturing') ?>>Manufacturing</option>
                        <option value="Energy"<?= $isSelected('field', 'Energy') ?>>Energy</option>
                        <option value="Transportation"<?= $isSelected('field', 'Transportation') ?>>Transportation</option>
                        <option value="Entertainment"<?= $isSelected('field', 'Entertainment') ?>>Entertainment</option>
                        <option value="Sports"<?= $isSelected('field', 'Sports') ?>>Sports</option>
                    </select>
                </div>
                <div class="field_set">
                    <label for="experience">Years of Experience:</label>
                    <input type="number" name="experience" id="experience" placeholder="i.e 5" value="<?= $escape($oldInput['experience'] ?? '') ?>">
                </div>
                <div class="field_set">
                    <label for="organization">Organization:</label>
                    <input type="text" name="organization" id="organization" placeholder="i.e XYZ Tech Solutions" value="<?= $escape($oldInput['organization'] ?? '') ?>">
                </div>
                <div class="field_set">
                    <label for="bio">Bio:</label>
                    <textarea name="bio" id="bio"
                        placeholder="Tell us about yourself and your entrepreneurial journey"><?= $escape($oldInput['bio'] ?? '') ?></textarea>
                </div>
                <!-- Link for personal website -->
                <div class="field_set">
                    <label for="personal_web">Personal Website:</label>
                    <input type="url" name="personal_web" id="personal_web" placeholder="i.e https://www.johndoe.com" value="<?= $escape($oldInput['personal_web'] ?? '') ?>">
                </div>

                <!-- File Upload Fields -->
                <div class="field_set">
                    <label for="profile_pic">Attach Profile Picture (<span class="span">Max 2MB</span>)</label>
                    <span class="span2">Allowed files: JPG, JPEG, PNG, GIF</span>
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/jpeg,image/png,image/gif" required>
                </div>

                <div class="field_set">
                    <label for="resume">Attach Resume (<span class="span">Max 2MB</span>)</label>
                    <span class="span2">Only .PDF or .DOCX allowed</span>
                    <input type="file" name="resume" id="resume" accept=".pdf,.docx" required>
                </div>

                <div class="field_set">
                    <label for="documents">Attach Experience Certificates (if any) (<span class="span">Max 2MB</span>)</label>
                    <span class="span2">Only .PDF or .DOCX allowed</span>
                    <input type="file" name="documents" id="documents" accept=".pdf,.docx">
                </div>

                <div class="button_group">
                    <button type="reset" id="reset_btn"
                        onclick="document.getElementById('profile_form').reset();">Reset</button>
                    <button type="submit" id="submit_btn">Submit</button>
                </div>
            </form>

        </section>


    </div>
    <section id="footer">
        <p>© 2025 BizLaunchHub. All Rights Reserved.</p>
    </section>

    <script src="../../public/assets/JS/siderbar.js"></script>
    <script>
        const selectElement = document.getElementById("country");
        const previousCountry = <?php echo json_encode($oldInput['country'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

        async function fetchCountries() {
            try {
                const response = await fetch("../api/get_countries.php");

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();

                // Clear default option
                selectElement.innerHTML = '<option value="none">-- Select --</option>';

                data.forEach(country => {
                    const commonName = country.name?.common || country.name;
                    if (commonName) {
                        const option = document.createElement("option");
                        option.value = commonName;
                        option.textContent = commonName;
                        selectElement.appendChild(option);
                    }
                });
                if (previousCountry) {
                    selectElement.value = previousCountry;
                }
            } catch (error) {
                console.error("Error fetching countries:", error);
                selectElement.innerHTML = '<option value="none">Failed to load countries</option>';

                // Optional fallback: Hardcode a small list (Pakistan first for your users)
                const fallback = ["Pakistan", "India", "Afghanistan", "Bangladesh", "United States", "United Kingdom", "Canada", "Australia"];
                fallback.forEach(name => {
                    const opt = document.createElement("option");
                    opt.value = name;
                    opt.textContent = name;
                    selectElement.appendChild(opt);
                });
                if (previousCountry) {
                    selectElement.value = previousCountry;
                }
            }
        }

        fetchCountries();
    </script>
</body>

</html>