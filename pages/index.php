<?php
require_once("../api/config/db.php");
include("header.php"); 

$conn = (new Database())->connect();

// FETCH FEATURED PROJECT (Keep this, it's good)
$featured_sql = "
    SELECT p.*, u.full_name AS student_name, COUNT(pl.like_id) AS total_likes
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN project_likes pl ON pl.project_id = p.project_id
    WHERE p.status = 'approved'
    GROUP BY p.project_id
    ORDER BY total_likes DESC, p.date_submitted DESC
    LIMIT 1
";
$featured_result = $conn->query($featured_sql);
$featured = $featured_result ? $featured_result->fetch_assoc() : null;
?>

<main id="home">

    <section class="hero-banner">
        <div class="hero-overlay"></div>
        <div class="hero-inner">
            <h1 class="hero-title">Sebastinian <span class="gold-gradient-text">Showcase</span></h1>
            <p class="hero-tagline">
                The central repository for academic excellence, innovation, and student research.
            </p>
            <a href="#repository" class="hero-cta">Browse Repository</a>
        </div>
    </section>

    <?php if ($featured): ?>
    <section class="featured-wrapper">
        <h2 class="section-heading">Spotlight Research</h2>
        <article class="featured-card">
            <div class="featured-media">
                <?php if (!empty($featured['image'])): ?>
                    <img src="../uploads/project_images/<?= htmlspecialchars($featured['image']) ?>" alt="Cover">
                <?php else: ?>
                    <div class="media-fallback"><i class="fas fa-book-open"></i></div>
                <?php endif; ?>
            </div>
            <div class="featured-details">
                <h3 class="featured-title"><?= htmlspecialchars($featured['title']) ?></h3>
                <p class="featured-author">By <?= htmlspecialchars($featured['student_name']) ?></p>
                <p class="featured-summary"><?= htmlspecialchars(substr($featured['description'], 0, 200)) ?>...</p>
                <div class="meta-tags">
                    <span class="meta-tag gold"><?= htmlspecialchars($featured['research_type'] ?? 'Research') ?></span>
                    <span class="meta-tag"><?= htmlspecialchars($featured['department'] ?? 'General') ?></span>
                </div>
                <a href="project.php?id=<?= $featured['project_id'] ?>" class="featured-link">Read Full Study</a>
            </div>
        </article>
    </section>
    <?php endif; ?>

    <section id="repository" class="repository-section">
        <div class="container">
            <h2 class="section-heading">Search Repository</h2>
            
            <div class="search-toolbar glass-panel">
                
                <div class="search-bar-wrapper">
                    <input type="text" id="searchInput" placeholder="Search title, author, or keywords...">
                </div>

                <div class="filters-row">
                    
                    <div class="filter-group">
                        <label><i class="fas fa-microscope"></i> Methodology</label>
                        <select id="filterType">
                            <option value="all">All Methodologies</option>
                            <option value="Quantitative">Quantitative</option>
                            <option value="Qualitative">Qualitative</option>
                            <option value="Mixed Methods">Mixed Methods</option>
                            <option value="Experimental">Experimental</option>
                            <option value="Descriptive">Descriptive</option>
                            <option value="Case Study">Case Study</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-university"></i> Department</label>
                        <select id="filterDept">
                            <option value="all">All Departments</option>
                            
                            <optgroup label="College - Business & Management">
                                <option value="BS Accountancy">BS Accountancy</option>
                                <option value="BSBA Financial Management">Financial Management</option>
                                <option value="BSBA Marketing Management">Marketing Management</option>
                                <option value="BSBA HRDM">Human Resource Dev. Mgmt</option>
                                <option value="BS Hospitality Management">Hospitality Management</option>
                                <option value="BS Tourism Management">Tourism Management</option>
                            </optgroup>

                            <optgroup label="College - Engineering & IT">
                                <option value="BS Information Technology">Information Technology</option>
                                <option value="BS Computer Engineering">Computer Engineering</option>
                                <option value="BS Industrial Engineering">Industrial Engineering</option>
                                <option value="BS Electronics Engineering">Electronics Engineering</option>
                            </optgroup>

                            <optgroup label="College - Arts, Sciences & Health">
                                <option value="BS Psychology">Psychology</option>
                                <option value="AB Communication">AB Communication</option>
                                <option value="BS Criminology">Criminology</option>
                                <option value="BS Nursing">Nursing</option>
                            </optgroup>

                            <optgroup label="Senior High School">
                                <option value="SHS - STEM">STEM</option>
                                <option value="SHS - ABM">ABM</option>
                                <option value="SHS - HUMSS">HUMSS</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="filter-group small">
                        <label><i class="fas fa-calendar"></i> Year</label>
                        <select id="filterYear">
                            <option value="all">All Years</option>
                            <?php 
                            $currentYear = date("Y");
                            for($y = $currentYear; $y >= 2020; $y--) {
                                echo "<option value='$y'>$y</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group small">
                        <label><i class="fas fa-sort"></i> Sort</label>
                        <select id="sortOrder">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="az">A-Z (Title)</option>
                            <option value="za">Z-A (Title)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="resultsGrid" class="gallery-grid">
                <div class="loading-spinner">
                     <i class="fas fa-circle-notch fa-spin"></i> Loading Repository...
                 </div>
            </div>

            <div id="noResults" class="no-results" style="display: none;">
                <i class="fas fa-folder-open"></i>
                <h3>No Research Found</h3>
                <p>Try adjusting your filters or search terms.</p>
            </div>
            
        </div>
    </section>

</main>

<style>
/* --- Global Section Headings --- */
.section-heading {
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    color: #800000; /* Sebastinian Red */
    text-align: center;
    margin-bottom: 40px;
    position: relative;
    padding-bottom: 15px;
}

.section-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: #D4AF37; /* Gold Underline */
    border-radius: 2px;
}

/* --- Spotlight Research (Featured Card) --- */
.featured-wrapper {
    padding: 60px 20px;
    background: #f9f9f9;
}

.featured-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    max-width: 1100px;
    margin: 0 auto;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    border-left: 6px solid #D4AF37; /* Gold Accent Bar */
}

.featured-media {
    height: 100%;
    min-height: 400px;
}

.featured-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.featured-details {
    padding: 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: linear-gradient(to right, #ffffff, #fffdfa);
}

.featured-title {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    color: #333;
    margin-bottom: 10px;
}

.featured-author {
    color: #800000; /* Red Author Name */
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 1px;
    margin-bottom: 20px;
}

.featured-link {
    display: inline-block;
    background: #800000;
    color: #fff;
    padding: 12px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 30px;
    width: fit-content;
    transition: all 0.3s ease;
    border: 1px solid #800000;
}

.featured-link:hover {
    background: #D4AF37; /* Turns Gold on Hover */
    border-color: #D4AF37;
    color: #000;
    transform: translateY(-3px);
}

/* --- Search Repository Section --- */
.repository-section {
    padding: 80px 0;
}

.search-toolbar {
    padding: 40px;
    margin-bottom: 50px;
    border-radius: 20px;
    background: #fff;
    border-top: 5px solid #800000; /* Red Top Bar like Header */
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.search-bar-wrapper .search-icon {
    color: #D4AF37 !important; /* Gold Search Icon */
}

.search-bar-wrapper input:focus {
    border-color: #D4AF37 !important;
    box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1) !important;
}

/* 1. Updated Row to Force Horizontal Layout */
.filters-row { 
    display: flex; 
    flex-wrap: nowrap; /* Prevents wrapping on desktop */
    gap: 15px; 
    align-items: flex-end; /* Aligns dropdowns with their labels */
    width: 100%;
}

/* 2. Make each group flexible but stay in line */
.filter-group { 
    flex: 1; /* Makes them all equal width */
    min-width: 120px; /* Prevents them from getting too squashed */
}

/* 3. Small classes for Year and Sort to take up less room */
.filter-group.small {
    flex: 0.6; 
}

/* 4. Ensure labels don't push things down */
.filter-group label { 
    display: block; 
    font-size: 0.75rem; 
    color: #800000; 
    margin-bottom: 6px; 
    font-weight: 700;
    white-space: nowrap; /* Keeps text on one line */
}

.filter-group select { 
    width: 100%; 
    padding: 12px; 
    border-radius: 8px; 
    border: 1px solid #ddd; 
    background: #fff;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

/* Mobile Fix: Allow wrapping only on small screens */
@media (max-width: 900px) {
    .filters-row {
        flex-wrap: wrap;
    }
    .filter-group {
        flex: 1 1 45%; /* Two per row on tablets */
    }
}

/* --- Meta Tags Styling --- */
.meta-tag.gold {
    background: rgba(212, 175, 55, 0.1);
    color: #8a6d0f;
    border: 1px solid rgba(212, 175, 55, 0.3);
    font-weight: 600;
}

/* --- Project Grid Cards (The results) --- */
.project-card {
    background: #fff;
    border-radius: 12px;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.project-card:hover {
    border-bottom: 3px solid #D4AF37; /* Gold bottom underline on hover */
    transform: translateY(-5px);
}

/* Responsive Fixes */
@media (max-width: 768px) {
    .featured-card { grid-template-columns: 1fr; }
    .featured-media { min-height: 250px; }
    .featured-details { padding: 30px; }
}
</style>

<?php include("footer.php"); ?>