<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title>
    <link rel="stylesheet" href="../../public/assets/CSS/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Miranda+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../components/_header.php'; ?>
    <div id="container">
        <?php include __DIR__ . '/../../components/_sidebar.php'; ?>
        <section id="main_content">
            <div class="title_bar">
                <h1>About Us</h1>
            </div>

            <div class="about_content">
                <section class="project-scope">
                    <h2>Scope of the Project</h2>
                    <p>The scope of this project is to design and develop an interactive web portal that serves as a comprehensive digital platform for aspiring entrepreneurs. The system aims to bridge the gap between business ideas and successful execution by providing access to essential resources such as mentorship, business planning tools, educational material, and networking opportunities.</p>

                    <p>The platform will support three main types of users: <strong>End Users (Entrepreneurs)</strong>, <strong>Agents (Mentors/Experts)</strong>, and <strong>Administrators</strong>. Each user type will have defined functionalities tailored to their roles. The web portal will allow aspiring entrepreneurs to explore business ideas, frameworks, and market opportunities while also enabling them to connect with experienced mentors for guidance.</p>

                    <p>The system will enhance entrepreneurship development by:</p>
                    <ul>
                        <li>Providing a centralized platform for business resources and templates.</li>
                        <li>Enabling mentor–entrepreneur interaction through forums and messaging features.</li>
                        <li>Offering business category search, mentorship listings, and startup-related materials.</li>
                        <li>Allowing admins to monitor content, manage mentors, and ensure data accuracy.</li>
                    </ul>

                    <p>This project's scope includes both front-end development (HTML, CSS, JavaScript, Bootstrap) for a user-friendly interface and back-end development (PHP and MySQL) for secure data handling, authentication, and content management. The final product will serve as a one-stop platform to educate, inspire, and empower young entrepreneurs.</p>
                </section>

                <section class="requirements">
                    <h2>Functional and Non-Functional Requirements</h2>

                    <h3>Functional Requirements</h3>

                    <div class="user-requirements">
                        <h4>1. End-User</h4>
                        <p>The User can:</p>
                        <ul>
                            <li>Sign-Up and Sign-In to the portal.</li>
                            <li>Search for business categories and mentors.</li>
                            <li>View mentor profiles with complete details.</li>
                            <li>Explore business ideas, frameworks, market research, and startup opportunities.</li>
                            <li>Access templates, partnership details, and financial support information.</li>
                            <li>Provide feedback to mentors or the system.</li>
                        </ul>
                    </div>

                    <div class="agent-requirements">
                        <h4>2. Entrepreneur / Agent</h4>
                        <p>The Entrepreneur/Agent can:</p>
                        <ul>
                            <li>Register and submit information for admin approval.</li>
                            <li>Upon approval, login to upload or manage business-related content.</li>
                            <li>Post business ideas, frameworks, opportunities, and templates.</li>
                            <li>Participate in discussion forums and private messaging.</li>
                            <li>Provide relevant contacts and resources to users.</li>
                        </ul>
                    </div>

                    <div class="admin-requirements">
                        <h4>3. Admin</h4>
                        <p>The admin can:</p>
                        <ul>
                            <li>Login to manage the overall system.</li>
                            <li>Approve or reject entrepreneur/agent registrations.</li>
                            <li>Manage user accounts, business content, and uploaded materials.</li>
                            <li>Create, edit, or delete tutorials and events.</li>
                            <li>Provide feedback to agents and issue login credentials.</li>
                        </ul>
                    </div>

                    <h3>Non-Functional Requirements</h3>

                    <div class="non-functional-requirements">
                        <div class="requirement-item">
                            <h4>1. Performance Requirements</h4>
                            <ul>
                                <li>The system should respond to user requests within 2–3 seconds.</li>
                                <li>Support concurrent access for multiple users without performance degradation.</li>
                            </ul>
                        </div>

                        <div class="requirement-item">
                            <h4>2. Security Requirements</h4>
                            <ul>
                                <li>User data, including login credentials, must be stored securely using encryption.</li>
                                <li>Only authenticated users can access restricted areas (e.g., agent dashboard, admin panel).</li>
                            </ul>
                        </div>

                        <div class="requirement-item">
                            <h4>3. Usability Requirements</h4>
                            <ul>
                                <li>The interface should be intuitive and easy to navigate for all user types.</li>
                                <li>Provide responsive design for mobile and desktop compatibility.</li>
                            </ul>
                        </div>

                        <div class="requirement-item">
                            <h4>4. Reliability Requirements</h4>
                            <ul>
                                <li>The system must ensure consistent uptime and minimal downtime during maintenance.</li>
                                <li>Backup mechanisms should be implemented to prevent data loss.</li>
                            </ul>
                        </div>

                        <div class="requirement-item">
                            <h4>5. Scalability Requirements</h4>
                            <ul>
                                <li>The system should be able to handle an increasing number of users, mentors, and content items efficiently.</li>
                            </ul>
                        </div>

                        <div class="requirement-item">
                            <h4>6. Maintainability Requirements</h4>
                            <ul>
                                <li>The codebase should be modular and well-documented for future updates or feature additions.</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </div>


    <script src="../../public/assets/JS/siderbar.js"></script>
</body>

</html>