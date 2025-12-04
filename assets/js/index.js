document.addEventListener("DOMContentLoaded", function() {
    const projectContainer = document.querySelector(".projects-grid");
    const sdgFilters = document.querySelectorAll(".sdg-menu a");
    const searchInput = document.getElementById("search-projects");

    let allProjects = []; // Store fetched projects from API

    // --- DEBOUNCE FUNCTION FOR SEARCH ---
    function debounce(func, wait = 300) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // --- FUNCTION TO LOAD PROJECTS FROM API ---
    async function fetchProjects() {
        try {
            const response = await fetch("../api/projects/get_projects.php");
            const projects = await response.json();

            if (!Array.isArray(projects)) {
                throw new Error("Invalid response from server");
            }

            allProjects = projects;
        } catch (error) {
            console.error("Error fetching projects:", error);
            projectContainer.innerHTML = `<p class="no-projects">Failed to load projects.</p>`;
        }
    }

    // --- FUNCTION TO RENDER PROJECT CARDS ---
    function renderProjects(projects) {
        if (!projects || projects.length === 0) {
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

    // --- FILTER PROJECTS BASED ON SDG AND SEARCH ---
    function filterProjects(filter = "", search = "") {
        let filtered = [...allProjects];

        if (filter) {
            filtered = filtered.filter(p => p.sdg_name === filter);
        }

        if (search) {
            const lowerSearch = search.toLowerCase();
            filtered = filtered.filter(p => p.title.toLowerCase().includes(lowerSearch));
        }

        renderProjects(filtered);
    }

    // --- SDG FILTER EVENT ---
    if (sdgFilters.length > 0) {
        sdgFilters.forEach(link => {
            link.addEventListener("click", e => {
                e.preventDefault();
                const filter = link.dataset.sdg || "";
                const search = searchInput ? searchInput.value.trim() : "";
                filterProjects(filter, search);
            });
        });
    }

    // --- LIVE SEARCH EVENT ---
    if (searchInput) {
        searchInput.addEventListener("input", debounce(() => {
            const search = searchInput.value.trim();
            // Find currently active SDG filter
            const activeFilterLink = document.querySelector(".sdg-menu a.active");
            const filter = activeFilterLink ? activeFilterLink.dataset.sdg || "" : "";
            filterProjects(filter, search);
        }, 250));
    }

    // --- INITIAL LOAD ---
    (async () => {
        await fetchProjects();
        // Don't overwrite initial PHP-rendered projects unless JS filtering/search is used
    })();
});
