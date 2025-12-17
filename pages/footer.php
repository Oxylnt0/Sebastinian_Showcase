<?php
// footer.php
$base = '/Sebastinian_Showcase/pages';
$assets = '/Sebastinian_Showcase/assets';
?>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-left">
            <h3>Sebastinian Showcase</h3>
            <p class="tagline">Excellence in Academia</p>
            <p class="copyright">&copy; <?php echo date("Y"); ?> San Sebastian College–Recoletos de Cavite.<br>All Rights Reserved.</p>
        </div>

        <div class="footer-right">
            <h4>Contact Us</h4>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <a href="mailto:info@sebastinian.edu.ph">info@sebastinian.edu.ph</a>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span>+63 123 456 7890</span>
            </div>
            
            <div class="social-icons">
                <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Made with <span class="heart">❤️</span> for Sebastinian Students & aligned with UNSDG Goals.</p>
    </div>

    <style>
        /* 1. Main Footer Container */
        .site-footer {
            /* Matches Header Gradient */
            background: linear-gradient(90deg, #800000 0%, #A52A2A 100%);
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            padding: 60px 0 0 0;
            position: relative;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
        }

        .footer-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px 40px 40px;
            gap: 40px;
        }

        /* 2. Typography & Headings */
        .site-footer h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #ffffff;
            margin-bottom: 5px;
        }

        .site-footer h4 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: #D4AF37; /* Gold Accent */
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        /* Gold underline under headings */
        .site-footer h4::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -5px;
            width: 40px;
            height: 2px;
            background: #D4AF37;
        }

        /* 3. Sections Styling */
        .footer-left {
            flex: 1.5;
            min-width: 250px;
        }
        
        .tagline {
            color: #D4AF37;
            font-weight: 500;
            margin-bottom: 15px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .copyright {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }

        .footer-center {
            flex: 1;
            min-width: 150px;
        }

        .footer-center ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-center ul li {
            margin-bottom: 10px;
        }

        .footer-center a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .footer-center a:hover {
            color: #D4AF37;
            padding-left: 5px; /* Slight slide effect */
        }

        .footer-right {
            flex: 1;
            min-width: 250px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.9rem;
        }
        
        .contact-item i {
            color: #D4AF37;
        }
        
        .contact-item a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .contact-item a:hover {
            color: #D4AF37;
        }

        /* 4. Social Icons */
        .social-icons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .social-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .social-icon:hover {
            background: #D4AF37;
            border-color: #D4AF37;
            color: #800000;
            transform: translateY(-3px);
        }

        /* 5. Bottom Section */
        .footer-bottom {
            background: rgba(0, 0, 0, 0.2); /* Slightly darker shade */
            text-align: center;
            padding: 20px;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            border-top: 1px solid rgba(212, 175, 55, 0.2); /* Thin gold line */
        }

        .heart {
            color: #ff4d4d;
            animation: beat 1.5s infinite;
            display: inline-block;
        }

        @keyframes beat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .footer-container {
                flex-direction: column;
                gap: 30px;
            }
        }

        /* Force icons to use the FontAwesome font family regardless of other CSS files */
        .site-footer i.fas, 
        .site-footer i.fab, 
        .site-footer i.fa-solid, 
        .site-footer i.fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
        }

    </style>
</footer>