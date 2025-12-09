<?php
// footer.php
$base = '/Sebastinian_Showcase/pages';
$assets = '/Sebastinian_Showcase/assets';
?>

<footer class="site-footer">
    <div class="footer-container">
        <!-- Left Section: Branding -->
        <div class="footer-left">
            <h3>Sebastinian Showcase</h3>
            <p>&copy; <?php echo date("Y"); ?> San Sebastian College–Recoletos de Cavite. All Rights Reserved.</p>
        </div>

        <!-- Center Section: Quick Links -->
        <div class="footer-center">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= $base ?>/index.php">Home</a></li>
                <li><a href="<?= $base ?>/about.php">About</a></li>
                <li><a href="<?= $base ?>/profile.php">Profile</a></li>
            </ul>
        </div>

        <!-- Right Section: Contact & Socials -->
        <div class="footer-right">
            <h4>Contact Us</h4>
            <p>Email: <a href="mailto:info@sebastinian.edu.ph">info@sebastinian.edu.ph</a></p>
            <p>Phone: +63 123 456 7890</p>
            <div class="social-icons">
                <a href="#" class="social-icon fb">FB</a>
                <a href="#" class="social-icon tw">TW</a>
                <a href="#" class="social-icon ig">IG</a>
            </div>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="footer-bottom">
        <p>Made with <span class="heart">❤️</span> for Sebastinian Students & aligned with UNSDG Goals.</p>
    </div>

    <!-- Footer CSS -->
    <link rel="stylesheet" href="<?= $assets ?>/css/footer.css">
</footer>
