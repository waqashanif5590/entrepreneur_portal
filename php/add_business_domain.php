<?php
include '../database.php';
include './tables/create_idea_table.php';
if (isset($_SESSION['loggedin']) || $_SESSION['loggedin'] == true) {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        $idea_title = htmlspecialchars($_POST["idea_title"], ENT_QUOTES, 'UTF-8');
        $idea_category = htmlspecialchars($_POST["idea_category"], ENT_QUOTES, 'UTF-8');
        $other_category = htmlspecialchars($_POST["other_category"], ENT_QUOTES, 'UTF-8');
        if ($idea_category == "others") {
            $idea_category = $other_category;
        }
        $idea_stage = htmlspecialchars($_POST["idea_stage"], ENT_QUOTES, 'UTF-8');
        $problem_statement = htmlspecialchars($_POST["problem"], ENT_QUOTES, 'UTF-8');
        $problem_solution = htmlspecialchars($_POST["solution"], ENT_QUOTES, 'UTF-8');
        $proposition_value = htmlspecialchars($_POST["value"], ENT_QUOTES, 'UTF-8');
        $target_market = htmlspecialchars($_POST["target_market"], ENT_QUOTES, 'UTF-8');
        $market_size = htmlspecialchars($_POST["market_size"], ENT_QUOTES, 'UTF-8');
        $business_model = htmlspecialchars($_POST["model"], ENT_QUOTES, 'UTF-8');
        $resources = htmlspecialchars($_POST["resources"], ENT_QUOTES, 'UTF-8');
        $outcomes = htmlspecialchars($_POST["outcomes"], ENT_QUOTES, 'UTF-8');
        $keywords = htmlspecialchars($_POST["keywords"], ENT_QUOTES, 'UTF-8');
        $visibility = htmlspecialchars($_POST["visibility"], ENT_QUOTES, 'UTF-8');
        $agent_profile_id = $_SESSION['id'];
        // if any field is none or empty, then show error
        if ($idea_category == "none" || $idea_stage == "none" || $visibility == "none") {
            $alert = "❌ Please fill/select all the required fields.";
            header("Location: add_business_domain.php?alert=$alert");
            exit;
        }

        $sql = "INSERT INTO `business_ideas` (idea_title, idea_category, idea_stage, problem_statement, problem_solution, proposition_value, target_market, market_size, business_model, resources, outcomes, keywords, visibility, agent_profile_id) VALUES ('$idea_title', '$idea_category', '$idea_stage', '$problem_statement', '$problem_solution', '$proposition_value', '$target_market', '$market_size', '$business_model', '$resources', '$outcomes', '$keywords', '$visibility', '$agent_profile_id')";

        $result = mysqli_query($conn, $sql);
        if (!$result) {
            echo "Data insertion failed" . mysqli_error($conn);
        } else {
            $alert = "✅ Business idea added Successfully.";
            header("Location: agent_portal.php?alert=$alert");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New business domain</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/add_businessdomain.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap"
        rel="stylesheet">
</head>

<body>
    <?php include 'partials/_header.php'; ?>
    <div id="container">
        <?php include 'partials/_sidebar.php'; ?>
        <section id="main_content">

            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                $author_id = $_SESSION['id'];
            } else {
                die('You must be logged in to access this page');
            }
            if (isset($_GET['alert'])) {
                $alert = $_GET['alert'];
                echo '<p id="alert_message">' . $alert . '</p>';
                unset($alert);
            }
            ?>
            <h2>Add New Business Idea</h2>


            <form action="./add_business_domain.php" method="post">
                <div class="field_set">
                    <label>Business Idea Title *</label>
                    <input type="text" class="idea_input" name="idea_title" placeholder="Enter business idea title"
                        required />
                </div>


                <div class="row">
                    <div class="field_set">
                        <label>Category / Industry *</label>
                        <select name="idea_category" required>
                            <option value="none">Select Category</option>
                            <option value="Technology">Technology</option>
                            <option value="Agriculture">Agriculture</option>
                            <option value="Health">Health</option>
                            <option value="Education">Education</option>
                            <option value="Retail">Retail</option>
                            <option value="Finance">Finance</option>
                            <option value="Energy">Energy</option>
                            <option value="Transportation">Transportation</option>
                            <option value="Food & Beverage">Food & Beverage</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Sports">Sports</option>
                            <option value="Real Estate">Real Estate</option>
                            <option value="Manufacturing">Manufacturing</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                    <div class="field_set">
                        <label>If the other Business Category *</label>
                        <input type="text" class="idea_input" name="other_category"
                            placeholder="Specify other category" />
                    </div>
                </div>
                <div class="field_set">
                    <label>Business Stage *</label>
                    <select name="idea_stage" required>
                        <option value="none">Select Stage</option>
                        <option value="Idea">Idea</option>
                        <option value="Prototype">Prototype</option>
                        <option value="Early Stage">Early Stage</option>
                        <option value="Growth">Growth</option>
                        <option value="Scaling">Scaling</option>
                    </select>
                </div>
                <div class="field_set">
                    <label>Problem Statement *</label>
                    <textarea name="problem" placeholder="Describe the problem" required></textarea>
                </div>

                <div class="field_set">
                    <label>Proposed Solution *</label>
                    <textarea name="solution" placeholder="Explain your solution" required></textarea>
                    <!-- <button class="add_btn">Save and Add New</button> -->

                    <!--  <div class="list">
                        <ul>
                            <div class="added_item">
                                <li>Telemedicine platform for remote consultations.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>AI-driven health monitoring apps.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>Affordable health insurance plans.</li>
                                <a href="#">Del</a>
                            </div>
                        </ul>
                    </div> -->
                </div>

                <div class="field_set">
                    <label>Unique Value Proposition *</label>
                    <textarea name="value" placeholder="What makes this idea unique?" required></textarea>
                    <!-- <button class="add_btn">Save and Add New</button>
                    <div class="list">
                        <ul>
                            <div class="added_item">
                                <li>Telemedicine platform for remote consultations.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>AI-driven health monitoring apps.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>Affordable health insurance plans.</li>
                                <a href="#">Del</a>
                            </div>
                        </ul>
                    </div> -->
                </div>

                <div class="field_set">
                    <label>Target Market / Customer Segment *</label>
                    <textarea name="target_market" placeholder="Describe your target market" required></textarea>
                    <!-- <button class="add_btn">Save and Add New</button>
                    <div class="list">
                        <ul>
                            <div class="added_item">
                                <li>Telemedicine platform for remote consultations.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>AI-driven health monitoring apps.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>Affordable health insurance plans.</li>
                                <a href="#">Del</a>
                            </div>
                        </ul>
                    </div> -->
                </div>

                <div class="field_set">
                    <label>Market Size / Opportunity (Optional)</label>
                    <input type="text" class="idea_input" name="market_size"
                        placeholder="e.g., Estimated market size" />
                </div>

                <div class="field_set">
                    <label>Business Model *</label>
                    <textarea name="model" placeholder="Explain how the idea will generate revenue" required></textarea>
                </div>

                <div class="field_set">
                    <label>Required Resources / Funding (Optional)</label>
                    <textarea name="resources" placeholder="Specify needed budget or resources"></textarea>
                    <!-- <button class="add_btn">Save and Add New</button>
                    <div class="list">
                        <ul>
                            <div class="added_item">
                                <li>Telemedicine platform for remote consultations.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>AI-driven health monitoring apps.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>Affordable health insurance plans.</li>
                                <a href="#">Del</a>
                            </div>
                        </ul>
                    </div> -->
                </div>

                <div class="field_set">
                    <label>Expected Outcomes / Goals (Optional)</label>
                    <textarea name="outcomes" placeholder="KPIs, milestones"></textarea>
                    <!-- <button class="add_btn">Save and Add New</button>
                    <div class="list">
                        <ul>
                            <div class="added_item">
                                <li>Telemedicine platform for remote consultations.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>AI-driven health monitoring apps.</li>
                                <a href="#">Del</a>
                            </div>
                            <div class="added_item">
                                <li>Affordable health insurance plans.</li>
                                <a href="#">Del</a>
                            </div>
                        </ul>
                    </div> -->
                </div>

                <div class="field_set">
                    <label>Keywords / Tags</label>
                    <input type="text" class="idea_input" name="keywords" placeholder="e.g., fintech, ai, e-commerce" />
                </div>

                <!-- <div class="field_set">
                    <label>Upload Your Photo *</label>
                    <input name="photo" type="file" required />
                </div>

                <div class="field_set">
                    <label>Attachments (Optional)</label>
                    <input name="attachments" type="file" />
                </div> -->

                <div class="field_set">
                    <label>Visibility *</label>
                    <select name="visibility" required>
                        <option value="none">-- Select --</option>
                        <option value="Public">Public</option>
                        <option value="Private">Private</option>
                        <option value="Mentors Only">Mentors Only</option>
                    </select>
                </div>

                <div class="submit_part">
                    <div class="checkbox_container">
                        <input type="checkbox" name="checkbox" id="checkbox" class="checkbox" required>
                        <label for="checkbox" class="label_confirm">I accept the <a href="./privacyPolicy.php">Privacy
                                Policy</a> and the <a href="./terms&condition.php">Terms of Service</a></label>
                    </div>

                    <button class="submit-btn" type="submit">Submit Business Idea</button>
                </div>
            </form>
        </section>

    </div>
    <?php include 'partials/_footer.php'; ?>

    <script src="../JS/siderbar.js"></script>
</body>

</html>