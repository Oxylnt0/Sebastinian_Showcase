<?php
session_start();
require_once("header.php");
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<main class="showcase-about">

    <section class="hero-section">
        <div class="hero-bg"></div> <div class="hero-overlay"></div>
        <div class="hero-container">
            <span class="hero-tag">Sebastinian Identity</span>
            <h1 class="hero-title">
                Excellence <span class="text-stroke">in</span> <br>
                <span class="text-gold">Innovation</span>
            </h1>
            <p class="hero-lead">
                Bridging the gap between academic brilliance and global impact. 
                We are the premier platform for student creativity aligned with the UN Sustainable Development Goals.
            </p>
            <div class="scroll-indicator">
                <div class="mouse"></div>
            </div>
        </div>
    </section>

    <div class="stats-banner">
        <div class="stat-item">
            <h3>SDG</h3>
            <p>Aligned</p>
        </div>
        <div class="vertical-line"></div>
        <div class="stat-item">
            <h3>100%</h3>
            <p>Student Led</p>
        </div>
        <div class="vertical-line"></div>
        <div class="stat-item">
            <h3>Future</h3>
            <p>Ready</p>
        </div>
    </div>

    <section class="visionary-section">
        <div class="container">
            <div class="vision-grid">
                <div class="vision-card main-card">
                    <div class="card-content">
                        <i class="fas fa-eye fa-3x icon-gold"></i>
                        <h2>Our Vision</h2>
                        <p>To rise as the definitive digital sanctuary for Sebastinian intellect—where academic outputs transform into real-world solutions. We envision a community that doesn't just learn history, but writes the future through social responsibility and sustainable innovation.</p>
                    </div>
                    <div class="card-bg-pattern"></div>
                </div>

                <div class="vision-card side-card">
                    <div class="card-content">
                        <i class="fas fa-bullseye fa-2x icon-red"></i>
                        <h3>Our Mission</h3>
                        <p>To foster a culture of holistic education by providing a dynamic stage for research, creative arts, and community initiatives.</p>
                    </div>
                </div>
                
                <div class="vision-card quote-card">
                    <blockquote>"Creativity is intelligence having fun."</blockquote>
                </div>
            </div>
        </div>
    </section>

    <section class="values-section">
        <div class="container">
            <h2 class="section-heading">Core <span class="highlight-red">Values</span></h2>
            <div class="bento-grid">
                <div class="bento-box box-large">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Creativity</h3>
                    <p>We don't just think outside the box; we redesign it. Innovation is our heartbeat.</p>
                </div>
                <div class="bento-box box-tall">
                    <i class="fas fa-hands-helping"></i>
                    <h3>Service</h3>
                    <p>Leadership that serves. Our projects are rooted in compassion.</p>
                </div>
                <div class="bento-box box-wide">
                    <div class="content-row">
                        <div class="text">
                            <h3>Collaboration</h3>
                            <p>Unity in diversity. Stronger together.</p>
                        </div>
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="bento-box box-standard">
                    <i class="fas fa-medal"></i>
                    <h3>Excellence</h3>
                    <p>Quality without compromise.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="impact-section">
        <div class="container">
            <div class="impact-header">
                <h2>Global <span class="text-gold">Impact</span></h2>
                <p>Targeting the United Nations SDGs.</p>
            </div>
            
            <div class="sdg-scroller">
                <div class="sdg-tile color-4">
                    <span class="num">04</span>
                    <span class="name">Quality Education</span>
                </div>
                <div class="sdg-tile color-9">
                    <span class="num">09</span>
                    <span class="name">Innovation</span>
                </div>
                <div class="sdg-tile color-11">
                    <span class="num">11</span>
                    <span class="name">Communities</span>
                </div>
                <div class="sdg-tile color-13">
                    <span class="num">13</span>
                    <span class="name">Climate Action</span>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once("footer.php"); ?>