<?php
require_once __DIR__ . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="./public/assets/CSS/style.css">
    <link rel="stylesheet" href="./public/assets/CSS/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <section id="header">
        <div class="logo">
            <img src="./public/assets/images/BZH7.png" alt="">
        </div>

        <div class="profile">
            <h1>BC220421769</h1>
            <div class="profile_icon">
                <img src="./public/assets/images/profile.png" alt="">
            </div>
        </div>
    </section>
    <div id="container">
        <section id="main_content">
            <section id="contentbar">
                <div class="video">
                    <video autoplay muted loop playsinline class="bg-video">
                        <source src="./videos/video_back.webm" type="video/webm">
                    </video>
                </div>
                <div class="content">
                    <h1>Empower Your Entrepreneurial Journey</h1>
                    <p>Connect with mentors, explore ideas, and access tools to build your dream business.</p>
                    <a href="./views/auth/login_dashboard.php">Get Started</a>
                </div>

            </section>
            <section id="home_content">
                <div class="search_bar">
                    <input type="text" id="search" placeholder="Search mentors, ideas, templates, resources...">
                    <div class="filter">
                        <label for="">Filter:</label>
                        <select name="select" id="select">
                            <option value="all">All Categories</option>
                            <option value="health">Health</option>
                            <option value="agri">Agriculture</option>
                            <option value="education">Education</option>
                            <option value="ecommerce">E-commerce</option>
                            <option value="food">Food</option>
                            <option value="fintech">FinTech</option>
                            <option value="logistics">Logistics</option>
                            <option value="energy">Energy</option>
                            <option value="tourism">Tourism</option>
                        </select>
                    </div>
                </div>
                <h1>Welcome to BizLaunchHub</h1>
                <p>Empowering your entrepreneurial journey — from idea to execution. Find mentors, resources, templates
                    and funding all in one place.</p>

                <div class="cards_container">
                    <div class="card">
                        <h2>Mentors</h2>
                        <p>Connect with experienced entrepreneurs and industry experts to guide your business journey.
                        </p>
                        <a href="./views/auth/login_dashboard.php">Explore <img src="./public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                    </div>
                    <div class="card">
                        <h2>Resources</h2>
                        <p>Access a wealth of resources including articles, guides, and tools to help you grow your
                            business.</p>
                        <a href="./views/auth/login_dashboard.php">Explore <img src="./public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                    </div>
                    <div class="card">
                        <h2>Templates</h2>
                        <p>Utilize business templates for plans, financials, and marketing to streamline your
                            operations.
                        </p>
                        <a href="./views/auth/login_dashboard.php">Explore <img src="./public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                    </div>
                    <div class="card">
                        <h2>Funding</h2>
                        <p>Explore funding options and connect with investors to secure the capital you need.</p>
                        <a href="./views/auth/login_dashboard.php">Explore <img src="./public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                    </div>
                </div>

                <div class="business_ideas">
                    <h1>Featured business ideas</h1>
                    <div class="cards_container">
                        <div class="card">
                            <h2>Solar-powered cold storage for farmers</h2>
                            <p>Reduces post-harvest loss — high demand in rural areas.</p>
                            <div class="idea_bottom_section">
                                <a href="./views/auth/login_dashboard.php">Learn More <img src="./public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                                <span>Agriculture</span>

                            </div>
                        </div>
                        <div class="card">
                            <h2>Low-cost e-commerce for artisans</h2>
                            <p>Marketplace and fulfillment solutions tailored to craftspeople.</p>
                            <div class="idea_bottom_section">
                                <a href="./views/auth/login_dashboard.php">Learn More <img src="./public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                                <span>E‑commerce</span>

                            </div>
                        </div>
                        <div class="card">
                            <h2>Mobile veterinary clinics</h2>
                            <p>Serves remote communities with livestock care and vaccinations.</p>
                            <div class="idea_bottom_section">
                                <a href="./views/auth/login_dashboard.php">Learn More <img src="./public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                                <span>Agriculture</span>

                            </div>
                        </div>
                        <div class="card">
                            <h2>Home-based healthy meal subscriptions</h2>
                            <p>Targeted at busy professionals and health-conscious eaters.</p>
                            <div class="idea_bottom_section">
                                <a href="./views/auth/login_dashboard.php">Learn More <img src="./public/assets/images/arrow_icon.png" alt="" class="arrow_icon"></a>
                                <span>Food</span>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="mentors_recommendations">
                    <h1>Mentor Recommendations</h1>
                    <div class="cards_container">
                        <div class="card">
                            <div class="mentor">
                                <div class="mentor_icon">
                                    <img src="./public/assets/images/profile.png" alt="">
                                </div>
                                <div class="mentor_profile">
                                    <h1>Jack Smith</h1>
                                    <p>Financial modeling & grants</p>
                                </div>
                            </div>
                            <a href="./views/auth/login_dashboard.php">Visit<img src="./public/assets/images/arrow_icon.png" alt=""
                                    class="arrow_icon"></a>
                        </div>
                        <div class="card">
                            <div class="mentor">
                                <div class="mentor_icon">
                                    <img src="./public/assets/images/profile.png" alt="">
                                </div>
                                <div class="mentor_profile">
                                    <h1>Chris Johnson</h1>
                                    <p>Marketing & Sales</p>
                                </div>
                            </div>
                            <a href="./views/auth/login_dashboard.php">Visit<img src="./public/assets/images/arrow_icon.png" alt=""
                                    class="arrow_icon"></a>
                        </div>
                        <div class="card">
                            <div class="mentor">
                                <div class="mentor_icon">
                                    <img src="./public/assets/images/profile.png" alt="">
                                </div>
                                <div class="mentor_profile">
                                    <h1>John Doe</h1>
                                    <p>Product Management</p>
                                </div>
                            </div>
                            <a href="./views/auth/login_dashboard.php">Visit<img src="./public/assets/images/arrow_icon.png" alt=""
                                    class="arrow_icon"></a>
                        </div>
                        <div class="card">
                            <div class="mentor">
                                <div class="mentor_icon">
                                    <img src="./public/assets/images/profile.png" alt="">
                                </div>
                                <div class="mentor_profile">
                                    <h1>Bob Johnson</h1>
                                    <p>Operations & Strategy</p>
                                </div>
                            </div>
                            <a href="./views/auth/login_dashboard.php">Visit<img src="./public/assets/images/arrow_icon.png" alt=""
                                    class="arrow_icon"></a>
                        </div>

                    </div>
                </div>
            </section>

        </section>

    </div>
    <section id="footer">
        <p>© 2025 Entrepreneur Portal. All Rights Reserved.</p>
    </section>
</body>

</html>