<?php
session_start();
require_once("header.php"); // Include header with navigation
?>

<main class="about-page">
    <section class="hero">
        <div class="hero-content">
            <h1>About Sebastinian Showcase</h1>
            <p>Empowering student creativity, promoting innovation, and aligning with the UNSDGs.</p>
        </div>
    </section>

    <section class="mission">
        <h2>Our Mission</h2>
        <p>
            The Sebastinian Showcase provides a dynamic platform where students can submit, display, 
            and highlight their academic outputs, research works, innovations, creative projects, 
            and community-based initiatives. Our mission is to foster creativity, collaboration, 
            and holistic education.
        </p>
    </section>

    <section class="vision">
        <h2>Our Vision</h2>
        <p>
            To become the premier digital showcase for Sebastinian students, inspiring innovation, 
            social responsibility, and excellence in education while supporting the United Nations 
            Sustainable Development Goals (SDGs).
        </p>
    </section>

    <section class="values">
        <h2>Core Values</h2>
        <ul>
            <li><strong>Creativity:</strong> Encouraging innovative thinking and originality.</li>
            <li><strong>Collaboration:</strong> Building community and teamwork among students and faculty.</li>
            <li><strong>Excellence:</strong> Striving for quality and impactful outcomes.</li>
            <li><strong>Responsibility:</strong> Aligning projects with social and environmental impact goals.</li>
        </ul>
    </section>

    <section class="sdgs-highlight">
        <h2>Aligned with UNSDGs</h2>
        <p>Our platform supports the following United Nations Sustainable Development Goals:</p>
        <ul>
            <li>SDG 4 – Quality Education</li>
            <li>SDG 9 – Industry, Innovation, and Infrastructure</li>
            <li>SDG 11 – Sustainable Cities & Communities</li>
            <li>SDG 13 – Climate Action</li>
        </ul>
    </section>
</main>

<?php
require_once("footer.php"); // Include footer
?>
