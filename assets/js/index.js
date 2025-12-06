document.addEventListener("DOMContentLoaded", function() {
    const projectContainer = document.querySelector(".projects-grid");

    let allProjects = []; // Store fetched projects from API

    // --- FUNCTION TO LOAD PROJECTS FROM API ---
    async function fetchProjects() {
        try {
            const res = await fetch("/Sebastinian_Showcase/api/admin/get_projects.php");
            const data = await res.json();

            if (data.status !== "success" || !data.data.projects) {
                throw new Error("Invalid response from server");
            }

            allProjects = data.data.projects;
            renderProjects(allProjects);
        } catch (error) {
            console.error("Error fetching projects:", error);
            projectContainer.innerHTML = `<p class="no-projects">Failed to load projects.</p>`;
        }
    }

    // --- FUNCTION TO RENDER PROJECT CARDS ---
    function renderProjects(projects) {
        if (!projects || projects.length === 0) {
            projectContainer.innerHTML = `<p class="no-projects">No projects available.</p>`;
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
                    <a href="project.php?id=${p.project_id}" class="view-btn">View Project</a>
                </div>
            </div>
        `).join("");
    }

    // --- INITIAL LOAD ---
    fetchProjects();
});
