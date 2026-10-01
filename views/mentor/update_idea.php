<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: ../auth/login_dashboard.php");
    exit;
}

$logged_in_id = $_SESSION['id'];

// Get and validate idea ID
if (!isset($_GET['business_idea_id']) || empty($_GET['business_idea_id'])) {
    die("Error: Business Idea ID is missing.");
}

$idea_id = base64_decode($_GET['business_idea_id']);

if (!is_numeric($idea_id)) {
    die("Error: Invalid Idea ID.");
}

// ==================== FETCH EXISTING BUSINESS IDEA ====================
$sql = "SELECT * FROM business_ideas WHERE id = '$idea_id' LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    die("Business idea not found.");
}

$row = mysqli_fetch_assoc($result);

// Security: Verify that this idea belongs to the logged-in user
if ($row['user_id'] != $logged_in_id) {
    die("You do not have permission to edit this business idea.");
}

// ==================== HANDLE FORM SUBMISSION ====================
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $idea_title        = mysqli_real_escape_string($conn, $_POST['idea_title'] ?? '');
    $idea_category     = mysqli_real_escape_string($conn, $_POST['idea_category'] ?? '');
    $other_category    = mysqli_real_escape_string($conn, $_POST['other_category'] ?? '');
    $idea_stage        = mysqli_real_escape_string($conn, $_POST['idea_stage'] ?? '');
    $problem_statement = mysqli_real_escape_string($conn, $_POST['problem'] ?? '');
    $problem_solution  = mysqli_real_escape_string($conn, $_POST['solution'] ?? '');
    $proposition_value = mysqli_real_escape_string($conn, $_POST['value'] ?? '');
    $target_market     = mysqli_real_escape_string($conn, $_POST['target_market'] ?? '');
    $market_size       = mysqli_real_escape_string($conn, $_POST['market_size'] ?? '');
    $business_model    = mysqli_real_escape_string($conn, $_POST['model'] ?? '');
    $resources         = mysqli_real_escape_string($conn, $_POST['resources'] ?? '');
    $outcomes          = mysqli_real_escape_string($conn, $_POST['outcomes'] ?? '');
    $keywords          = mysqli_real_escape_string($conn, $_POST['keywords'] ?? '');
    $visibility        = mysqli_real_escape_string($conn, $_POST['visibility'] ?? '');

    // Handle "Others" category
    if ($idea_category === 'others' && !empty($other_category)) {
        $idea_category = $other_category;
    }

    // Basic validation
    if (
        empty($idea_title) || empty($idea_category) || empty($idea_stage) ||
        empty($problem_statement) || empty($problem_solution) ||
        empty($proposition_value) || empty($target_market) ||
        empty($business_model) || empty($visibility)
    ) {

        $alert = "❌ Please fill all required fields.";
    } else {
        $update_sql = "UPDATE business_ideas SET 
            idea_title = '$idea_title',
            idea_category = '$idea_category',
            idea_stage = '$idea_stage',
            problem_statement = '$problem_statement',
            problem_solution = '$problem_solution',
            proposition_value = '$proposition_value',
            target_market = '$target_market',
            market_size = '$market_size',
            business_model = '$business_model',
            resources = '$resources',
            outcomes = '$outcomes',
            keywords = '$keywords',
            visibility = '$visibility'
            WHERE id = '$idea_id'";

        if (mysqli_query($conn, $update_sql)) {
            $alert = "✅ Business idea updated successfully!";
            header("Location: agent_portal.php?alert=" . urlencode($alert));
            exit;
        } else {
            $alert = "❌ Update failed: " . mysqli_error($conn);
        }
    }
}

$cancel_url = '../ideas/idea_details.php?business_idea_id=' . base64_encode($idea_id) . '&agent_profile_id=' . base64_encode($row['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Business Idea</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link rel="stylesheet" href="../../public/assets/CSS/add_businessdomain.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../components/_header.php'; ?>

    <div id="container">
        <?php include __DIR__ . '/../../components/_sidebar.php'; ?>

        <section id="main_content">
            <h2>Edit Business Idea</h2>

            <?php if (isset($alert)): ?>
                <div class="alert <?= strpos($alert, '✅') !== false ? 'success' : 'error' ?>">
                    <?= $alert ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <div class="field_set">
                    <label>Business Idea Title *</label>
                    <input type="text" name="idea_title"
                        value="<?= htmlspecialchars($row['idea_title'] ?? '') ?>" required>
                </div>

                <div class="row">
                    <div class="field_set">
                        <label>Category / Industry *</label>
                        <select name="idea_category" required>
                            <option value="Technology" <?= ($row['idea_category'] ?? '') === 'Technology' ? 'selected' : '' ?>>Technology</option>
                            <option value="Agriculture" <?= ($row['idea_category'] ?? '') === 'Agriculture' ? 'selected' : '' ?>>Agriculture</option>
                            <option value="Health" <?= ($row['idea_category'] ?? '') === 'Health' ? 'selected' : '' ?>>Health</option>
                            <option value="Education" <?= ($row['idea_category'] ?? '') === 'Education' ? 'selected' : '' ?>>Education</option>
                            <option value="Retail" <?= ($row['idea_category'] ?? '') === 'Retail' ? 'selected' : '' ?>>Retail</option>
                            <option value="others"
                                <?= !in_array(($row['idea_category'] ?? ''), ['Technology', 'Agriculture', 'Health', 'Education', 'Retail']) ? 'selected' : '' ?>>
                                Others
                            </option>
                        </select>
                    </div>

                    <div class="field_set">
                        <label>If Other Category</label>
                        <input type="text" name="other_category"
                            value="<?= htmlspecialchars($row['idea_category'] ?? '') ?>"
                            placeholder="Type here if others">
                    </div>
                </div>

                <div class="field_set">
                    <label>Business Stage *</label>
                    <select name="idea_stage" required>
                        <option value="Idea" <?= ($row['idea_stage'] ?? '') === 'Idea' ? 'selected' : '' ?>>Idea</option>
                        <option value="Prototype" <?= ($row['idea_stage'] ?? '') === 'Prototype' ? 'selected' : '' ?>>Prototype</option>
                        <option value="Early Stage" <?= ($row['idea_stage'] ?? '') === 'Early Stage' ? 'selected' : '' ?>>Early Stage</option>
                        <option value="Growth" <?= ($row['idea_stage'] ?? '') === 'Growth' ? 'selected' : '' ?>>Growth</option>
                        <option value="Scaling" <?= ($row['idea_stage'] ?? '') === 'Scaling' ? 'selected' : '' ?>>Scaling</option>
                    </select>
                </div>

                <div class="field_set">
                    <label>Problem Statement *</label>
                    <textarea name="problem" required><?= htmlspecialchars($row['problem_statement'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Proposed Solution *</label>
                    <textarea name="solution" required><?= htmlspecialchars($row['problem_solution'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Unique Value Proposition *</label>
                    <textarea name="value" required><?= htmlspecialchars($row['proposition_value'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Target Market / Customer Segment *</label>
                    <textarea name="target_market" required><?= htmlspecialchars($row['target_market'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Market Size (Optional)</label>
                    <input type="text" name="market_size"
                        value="<?= htmlspecialchars($row['market_size'] ?? '') ?>">
                </div>

                <div class="field_set">
                    <label>Business Model *</label>
                    <textarea name="model" required><?= htmlspecialchars($row['business_model'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Required Resources (Optional)</label>
                    <textarea name="resources"><?= htmlspecialchars($row['resources'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Expected Outcomes (Optional)</label>
                    <textarea name="outcomes"><?= htmlspecialchars($row['outcomes'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Keywords / Tags</label>
                    <input type="text" name="keywords"
                        value="<?= htmlspecialchars($row['keywords'] ?? '') ?>">
                </div>

                <div class="field_set">
                    <label>Visibility *</label>
                    <select name="visibility" required>
                        <option value="Public" <?= ($row['visibility'] ?? '') === 'Public' ? 'selected' : '' ?>>Public</option>
                        <option value="Private" <?= ($row['visibility'] ?? '') === 'Private' ? 'selected' : '' ?>>Private</option>
                        <option value="Mentors Only" <?= ($row['visibility'] ?? '') === 'Mentors Only' ? 'selected' : '' ?>>Mentors Only</option>
                    </select>
                </div>

                <div class="submit_part">
                    <a href="<?= htmlspecialchars($cancel_url) ?>" class="add_btn">Cancel</a>
                    <button type="submit" class="submit-btn">Update Business Idea</button>
                </div>
            </form>
        </section>
    </div>

    <?php include __DIR__ . '/../../components/_footer.php'; ?>
</body>

</html>