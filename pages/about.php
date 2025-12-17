<?php
session_start();
require_once("header.php");
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* New Styles for Course Grid */
    .course-matrix-section {
        padding: 80px 0;
        background: #fdfdfd;
        border-top: 1px solid #eee;
    }
    
    .matrix-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }

    .college-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border-left: 4px solid #D4AF37; /* Gold accent */
        transition: transform 0.3s ease;
    }

    .college-card:hover {
        transform: translateY(-5px);
    }

    .college-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .college-header i {
        font-size: 1.5rem;
        color: #800000; /* Sebastinian Red */
    }

    .college-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.25rem;
        color: #333;
        margin: 0;
    }

    .course-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .course-list li {
        position: relative;
        padding-left: 20px;
        margin-bottom: 10px;
        color: #666;
        font-size: 0.95rem;
        font-weight: 400;
    }

    .course-list li::before {
        content: "•";
        color: #D4AF37;
        font-weight: bold;
        position: absolute;
        left: 0;
    }
</style>

<main class="showcase-about">

    <section class="hero-section">
        <div class="hero-bg"></div> <div class="hero-overlay"></div>
        <div class="hero-container">
            <span class="hero-tag">Sebastinian Research</span>
            <h1 class="hero-title">
                Excellence <span class="text-stroke">in</span> <br>
                <span class="text-gold">Academia</span>
            </h1>
            <p class="hero-lead">
                Bridging the gap between theoretical knowledge and real-world application. 
                We are the premier digital repository for undergraduate theses, scholarly articles, and capstone research.
            </p>
        </div>
    </section>

    <div class="stats-banner">
        <div class="stat-item">
            <h3>Peer</h3>
            <p>Reviewed</p>
        </div>
        <div class="vertical-line"></div>
        <div class="stat-item">
            <h3>100%</h3>
            <p>Student Authors</p>
        </div>
        <div class="vertical-line"></div>
        <div class="stat-item">
            <h3>Open</h3>
            <p>Access</p>
        </div>
    </div>

    <section class="visionary-section">
        <div class="container">
            <div class="vision-grid">
                <div class="vision-card main-card">
                    <div class="card-content">
                        <i class="fas fa-book-reader fa-3x icon-gold"></i>
                        <h2>Our Vision</h2>
                        <p>To rise as the definitive digital archive for Sebastinian intellect—where academic outputs are preserved, shared, and cited. We envision a community that contributes to the global body of knowledge through rigorous inquiry and evidence-based study.</p>
                    </div>
                    <div class="card-bg-pattern"></div>
                </div>

                <div class="vision-card side-card">
                    <div class="card-content">
                        <i class="fas fa-university fa-2x icon-red"></i>
                        <h3>Our Mission</h3>
                        <p>To foster a culture of academic integrity by providing a centralized platform for the dissemination of student research and scholarly work.</p>
                    </div>
                </div>
                
                <div class="vision-card quote-card">
                    <blockquote>"Research is seeing what everybody else has seen and thinking what nobody else has thought."</blockquote>
                </div>
            </div>
        </div>
    </section>

    <section class="values-section">
        <div class="container">
            <h2 class="section-heading">Core <span class="highlight-red">Values</span></h2>
            <div class="bento-grid">
                <div class="bento-box box-large">
                    <i class="fas fa-brain"></i>
                    <h3>Critical Thinking</h3>
                    <p>We analyze, evaluate, and synthesize. Rigorous inquiry is our foundation.</p>
                </div>
                <div class="bento-box box-tall">
                    <i class="fas fa-search"></i>
                    <h3>Discovery</h3>
                    <p>Uncovering new truths. Our projects expand the boundaries of knowledge.</p>
                </div>
                <div class="bento-box box-wide">
                    <div class="content-row">
                        <div class="text">
                            <h3>Integrity</h3>
                            <p>Honesty in research. Stronger ethics, better results.</p>
                        </div>
                        <i class="fas fa-balance-scale"></i>
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

    <section class="course-matrix-section">
        <div class="container">
            <h2 class="section-heading" style="text-align: center;">Academic <span class="highlight-red">Departments</span></h2>
            <p style="text-align: center; color: #666;">Explore research from our specialized programs</p>
            
            <div class="matrix-grid">
                
                <div class="college-card">
                    <div class="college-header">
                        <i class="fas fa-briefcase"></i>
                        <h3>Business & Management</h3>
                    </div>
                    <ul class="course-list">
                        <li>BS Accountancy</li>
                        <li>BSBA Financial Management</li>
                        <li>BSBA Marketing Management</li>
                        <li>BSBA HRDM</li>
                        <li>BS Hospitality Management</li>
                        <li>BS Tourism Management</li>
                    </ul>
                </div>

                <div class="college-card">
                    <div class="college-header">
                        <i class="fas fa-microchip"></i>
                        <h3>Engineering & IT</h3>
                    </div>
                    <ul class="course-list">
                        <li>BS Information Technology</li>
                        <li>BS Computer Engineering</li>
                        <li>BS Industrial Engineering</li>
                        <li>BS Electronics Engineering</li>
                    </ul>
                </div>

                <div class="college-card">
                    <div class="college-header">
                        <i class="fas fa-heartbeat"></i>
                        <h3>Arts, Sciences & Health</h3>
                    </div>
                    <ul class="course-list">
                        <li>BS Psychology</li>
                        <li>AB Communication</li>
                        <li>BS Criminology</li>
                        <li>BS Nursing</li>
                    </ul>
                </div>

                <div class="college-card">
                    <div class="college-header">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>Senior High School</h3>
                    </div>
                    <ul class="course-list">
                        <li>STEM (Science, Tech, Eng, Math)</li>
                        <li>ABM (Accountancy, Business, Mgmt)</li>
                        <li>HUMSS (Humanities & Social Sci)</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

</main>

<?php require_once("footer.php"); ?>