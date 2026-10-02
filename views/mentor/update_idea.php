<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: ../auth/login_dashboard.php");
    exit;
}

$logged_in_id = $_SESSION['id'];
$viewerRole = requireAccountRole($conn, ['agent']);
$csrfToken = csrfToken();

// Get and validate idea ID
$idea_id = requestPositiveId($_GET['business_idea_id'] ?? null);
if ($idea_id === null) {
    die("Error: Invalid Idea ID.");
}

// ==================== FETCH EXISTING BUSINESS IDEA ====================
$ideaStatement = $conn->prepare('SELECT * FROM business_ideas WHERE id = ? LIMIT 1');
$ideaStatement->bind_param('i', $idea_id);
$ideaStatement->execute();
$result = $ideaStatement->get_result();

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    die("Business idea not found.");
}

$idea = mysqli_fetch_assoc($result);

// Security: Verify that this idea belongs to the logged-in user
if ((int)$idea['user_id'] !== (int)$logged_in_id) {
    http_response_code(403);
    exit("You do not have permission to edit this business idea.");
}

// ==================== HANDLE FORM SUBMISSION ====================
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    requirePostCsrfToken();

    $idea_title        = trim($_POST['idea_title'] ?? '');
    $idea_category     = trim($_POST['idea_category'] ?? '');
    $other_category    = trim($_POST['other_category'] ?? '');
    $idea_stage        = trim($_POST['idea_stage'] ?? '');
    $problem_statement = trim($_POST['problem'] ?? '');
    $problem_solution  = trim($_POST['solution'] ?? '');
    $proposition_value = trim($_POST['value'] ?? '');
    $target_market     = trim($_POST['target_market'] ?? '');
    $market_size       = trim($_POST['market_size'] ?? '');
    $business_model    = trim($_POST['model'] ?? '');
    $resources         = trim($_POST['resources'] ?? '');
    $outcomes          = trim($_POST['outcomes'] ?? '');
    $keywords          = trim($_POST['keywords'] ?? '');
    $visibility        = trim($_POST['visibility'] ?? '');

    // Handle "Others" category
    if ($idea_category === 'others' && !empty($other_category)) {
        $idea_category = $other_category;
    }

    // Basic validation
    if (
        empty($idea_title) || empty($idea_category) || empty($idea_stage) ||
        empty($problem_statement) || empty($problem_solution) ||
        empty($proposition_value) || empty($target_market) ||
        empty($business_model) || !in_array($visibility, ['Public', 'Private', 'Mentors Only'], true)
    ) {

        $alert = "❌ Please fill all required fields.";
    } else {
        $update = $conn->prepare('UPDATE business_ideas SET idea_title = ?, idea_category = ?, idea_stage = ?, problem_statement = ?, problem_solution = ?, proposition_value = ?, target_market = ?, market_size = ?, business_model = ?, resources = ?, outcomes = ?, keywords = ?, visibility = ? WHERE id = ? AND user_id = ?');
        $update->bind_param('sssssssssssssii', $idea_title, $idea_category, $idea_stage, $problem_statement, $problem_solution, $proposition_value, $target_market, $market_size, $business_model, $resources, $outcomes, $keywords, $visibility, $idea_id, $logged_in_id);
        if ($update->execute()) {
            $update->close();
            $alert = "✅ Business idea updated successfully!";
            header("Location: agent_portal.php?alert=" . urlencode($alert));
            exit;
        } else {
            $alert = "❌ Update failed.";
            $update->close();
        }
    }
}

$cancel_url = '../ideas/idea_details.php?business_idea_id=' . $idea_id . '&agent_profile_id=' . (int)$idea['user_id'];
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
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="field_set">
                    <label>Business Idea Title *</label>
                    <input type="text" name="idea_title"
                        value="<?= htmlspecialchars($idea['idea_title'] ?? '') ?>" required>
                </div>

                <div class="row">
                    <div class="field_set">
                        <label>Category / Industry *</label>
                        <select name="idea_category" required>
                            <option value="Technology" <?= ($idea['idea_category'] ?? '') === 'Technology' ? 'selected' : '' ?>>Technology</option>
                            <option value="Agriculture" <?= ($idea['idea_category'] ?? '') === 'Agriculture' ? 'selected' : '' ?>>Agriculture</option>
                            <option value="Health" <?= ($idea['idea_category'] ?? '') === 'Health' ? 'selected' : '' ?>>Health</option>
                            <option value="Education" <?= ($idea['idea_category'] ?? '') === 'Education' ? 'selected' : '' ?>>Education</option>
                            <option value="Retail" <?= ($idea['idea_category'] ?? '') === 'Retail' ? 'selected' : '' ?>>Retail</option>
                            <option value="others"
                                <?= !in_array(($idea['idea_category'] ?? ''), ['Technology', 'Agriculture', 'Health', 'Education', 'Retail']) ? 'selected' : '' ?>>
                                Others
                            </option>
                        </select>
                    </div>

                    <div class="field_set">
                        <label>If Other Category</label>
                        <input type="text" name="other_category"
                            value="<?= htmlspecialchars($idea['idea_category'] ?? '') ?>"
                            placeholder="Type here if others">
                    </div>
                </div>

                <div class="field_set">
                    <label>Business Stage *</label>
                    <select name="idea_stage" required>
                        <option value="Idea" <?= ($idea['idea_stage'] ?? '') === 'Idea' ? 'selected' : '' ?>>Idea</option>
                        <option value="Prototype" <?= ($idea['idea_stage'] ?? '') === 'Prototype' ? 'selected' : '' ?>>Prototype</option>
                        <option value="Early Stage" <?= ($idea['idea_stage'] ?? '') === 'Early Stage' ? 'selected' : '' ?>>Early Stage</option>
                        <option value="Growth" <?= ($idea['idea_stage'] ?? '') === 'Growth' ? 'selected' : '' ?>>Growth</option>
                        <option value="Scaling" <?= ($idea['idea_stage'] ?? '') === 'Scaling' ? 'selected' : '' ?>>Scaling</option>
                    </select>
                </div>

                <div class="field_set">
                    <label>Problem Statement *</label>
                    <textarea name="problem" required><?= htmlspecialchars($idea['problem_statement'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Proposed Solution *</label>
                    <textarea name="solution" required><?= htmlspecialchars($idea['problem_solution'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Unique Value Proposition *</label>
                    <textarea name="value" required><?= htmlspecialchars($idea['proposition_value'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Target Market / Customer Segment *</label>
                    <textarea name="target_market" required><?= htmlspecialchars($idea['target_market'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Market Size (Optional)</label>
                    <input type="text" name="market_size"
                        value="<?= htmlspecialchars($idea['market_size'] ?? '') ?>">
                </div>

                <div class="field_set">
                    <label>Business Model *</label>
                    <p class="info_text">Describe how your business will generate revenue and sustain itself.</p>
                    <textarea name="model" required><?= htmlspecialchars($idea['business_model'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Required Resources (Optional)</label>
                    <textarea name="resources"><?= htmlspecialchars($idea['resources'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Expected Outcomes (Optional)</label>
                    <textarea name="outcomes"><?= htmlspecialchars($idea['outcomes'] ?? '') ?></textarea>
                </div>

                <div class="field_set">
                    <label>Keywords / Tags</label>
                    <input type="text" name="keywords"
                        value="<?= htmlspecialchars($idea['keywords'] ?? '') ?>">
                </div>

                <div class="field_set">
                    <label>Visibility *</label>
                    <select name="visibility" required>
                        <option value="Public" <?= ($idea['visibility'] ?? '') === 'Public' ? 'selected' : '' ?>>Public</option>
                        <option value="Private" <?= ($idea['visibility'] ?? '') === 'Private' ? 'selected' : '' ?>>Private</option>
                        <option value="Mentors Only" <?= ($idea['visibility'] ?? '') === 'Mentors Only' ? 'selected' : '' ?>>Mentors Only</option>
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