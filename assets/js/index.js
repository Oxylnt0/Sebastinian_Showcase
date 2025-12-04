document.addEventListener("DOMContentLoaded", function() {
    const projectContainer = document.querySelector(".projects-grid");
    const sdgFilters = document.querySelectorAll(".sdg-menu a");
    const searchInput = document.getElementById("search-projects");

    // --- FUNCTION TO LOAD PROJECTS ---
    async function loadProjects(filter = "", search = "") {
        try {
            const response = await fetch("../api/projects/get_projects.php");
            const projects = await response.json();

            let filtered = projects;

            // Filter by SDG if specified
            if (filter) {
                filtered = filtered.filter(p => p.sdg_name === filter);
            }

            // Search by project title
            if (search) {
                filtered = filtered.filter(p => p.title.toLowerCase().includes(search.toLowerCase()));
            }

            renderProjects(filtered);
        } catch (error) {
            console.error("Error loading projects:", error);
            projectContainer.innerHTML = `<p class="no-projects">Failed to load projects.</p>`;
        }
    }

    // --- FUNCTION TO RENDER PROJECT CARDS ---
    function renderProjects(projects) {
        if (projects.length === 0) {
            projectContainer.innerHTML = `<p class="no-projects">No projects found.</p>`;
            return;
        }

        projectContainer.innerHTML = projects.map(p => `
            <div class="project-card">
                ${p.image ? 
                    `<img src="../uploads/project_images/${p.image}" alt="${p.title}">` :
                    `<div class="placeholder-img">No Image</div>`
                }
                <div class="project-content">
                    <h2>${p.title}</h2>
                    <p class="student-name">By: ${p.student_name}</p>
                    <p class="project-desc">${p.description.substring(0, 150)}...</p>
                    <span class="sdg-tag">${p.sdg_name}</span>
                    <a href="project.php?id=${p.project_id}" class="view-btn">View Project</a>
                </div>
            </div>
        `).join("");
    }

    // --- SDG FILTERING ---
    if (sdgFilters.length > 0) {
        sdgFilters.forEach(link => {
            link.addEventListener("click", e => {
                e.preventDefault();
                const filter = link.dataset.sdg || "";
                loadProjects(filter, searchInput ? searchInput.value : "");
            });
        });
    }

    // --- LIVE SEARCH ---
    if (searchInput) {
        searchInput.addEventListener("input", () => {
            const search = searchInput.value.trim();
            loadProjects("", search);
        });
    }

    // --- INITIAL LOAD ---
    loadProjects(); // Load all projects on page load
});
