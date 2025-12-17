// index.js — Research Repository Logic

document.addEventListener("DOMContentLoaded", () => {
    
    // ==========================================
    // 1. SEARCH & FILTER ENGINE
    // ==========================================
    const searchInput = document.getElementById('searchInput');
    const filterType  = document.getElementById('filterType');
    const filterDept  = document.getElementById('filterDept');
    const filterYear  = document.getElementById('filterYear');
    const sortOrder   = document.getElementById('sortOrder');
    const resultsGrid = document.getElementById('resultsGrid');
    const noResults   = document.getElementById('noResults');

    // Debounce function to prevent API spam while typing
    let debounceTimeout;
    const debounce = (func, delay) => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(func, delay);
    };

    // The Fetch Function
    const fetchProjects = async () => {
        // Show Loading State
        if (resultsGrid) {
            resultsGrid.innerHTML = `<div class="loading-spinner"><i class="fas fa-circle-notch fa-spin"></i> Searching Archives...</div>`;
            noResults.style.display = 'none';
        }

        // Build Query URL
        const params = new URLSearchParams({
            q: searchInput.value,
            type: filterType.value,
            dept: filterDept.value,
            year: filterYear.value,
            sort: sortOrder.value
        });

        try {
            const response = await fetch(`../api/projects/search.php?${params}`);
            const result = await response.json();

            if (result.success) {
                renderProjects(result.data);
            } else {
                console.error("Search Error:", result.message);
                resultsGrid.innerHTML = `<p style="text-align:center; color:red;">Error loading data.</p>`;
            }
        } catch (error) {
            console.error("Network Error:", error);
            resultsGrid.innerHTML = `<p style="text-align:center; color:red;">Network Error.</p>`;
        }
    };

    // The Render Function
    const renderProjects = (projects) => {
        resultsGrid.innerHTML = ''; // Clear loading

        if (projects.length === 0) {
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';

        projects.forEach(p => {
            // Safe fallbacks for missing data
            const title = p.title || 'Untitled';
            const author = p.author_name || 'Unknown Author';
            const desc = p.description ? (p.description.substring(0, 120) + '...') : 'No abstract available.';
            const image = p.image ? `../uploads/project_images/${p.image}` : null;
            const type = p.research_type || 'Research';
            const dept = p.department || 'General';
            const date = new Date(p.date_submitted).toLocaleDateString();

            // Build Card HTML
            const card = document.createElement('article');
            card.className = 'project-card fade-slide-in';
            
            let mediaHtml = image 
                ? `<img src="${image}" alt="${title}" loading="lazy">` 
                : `<div class="media-fallback"><i class="fas fa-microscope"></i></div>`;

            card.innerHTML = `
                <div class="project-media">
                    ${mediaHtml}
                    <div class="card-overlay">
                        <a href="project.php?id=${p.project_id}" class="project-link">Read Thesis</a>
                    </div>
                </div>
                <div class="project-info">
                    <div class="meta-tags" style="margin-bottom:10px;">
                        <span class="meta-tag gold">${type}</span>
                    </div>
                    <h3 class="project-title">${title}</h3>
                    <p class="project-author">By ${author}</p>
                    <p class="project-excerpt">${desc}</p>
                    <div class="project-footer" style="margin-top:15px; font-size:0.8rem; color:#aaa; display:flex; justify-content:space-between;">
                        <span><i class="fas fa-university"></i> ${dept}</span>
                        <span><i class="fas fa-calendar"></i> ${date}</span>
                    </div>
                </div>
            `;
            resultsGrid.appendChild(card);
        });
    };

    // Attach Event Listeners
    if(searchInput) {
        searchInput.addEventListener('input', () => debounce(fetchProjects, 500)); // Wait 500ms after typing
        filterType.addEventListener('change', fetchProjects);
        filterDept.addEventListener('change', fetchProjects);
        filterYear.addEventListener('change', fetchProjects);
        sortOrder.addEventListener('change', fetchProjects);

        // Initial Load
        fetchProjects(); 
    }

    // ==========================================
    // 2. HERO SCROLL (Keep existing logic)
    // ==========================================
    const heroCTA = document.querySelector(".hero-cta");
    if (heroCTA) {
        heroCTA.addEventListener("click", (e) => {
            e.preventDefault();
            document.querySelector("#repository").scrollIntoView({ behavior: "smooth" });
        });
    }

    // ... (Keep your Header Shadow and Back to Top logic here if you want) ...
});