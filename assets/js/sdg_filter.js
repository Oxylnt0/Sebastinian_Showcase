// sdg_filter.js

document.addEventListener("DOMContentLoaded", () => {
    const sdgSelect = document.getElementById("sdgSelect");
    const projectsContainer = document.querySelector(".projects-list");

    if (!sdgSelect || !projectsContainer) return;

    sdgSelect.addEventListener("change", async () => {
        const sdgId = sdgSelect.value;

        // Show loading state
        projectsContainer.innerHTML = `<p class="no-projects">Loading projects...</p>`;

        try {
            const response = await fetch("../api/projects/get_projects.php");
            if (!response.ok) throw new Error("Network response was not ok");

            const projects = await response.json();

            // Filter by SDG if selected
            const filteredProjects = sdgId == 0 
                ? projects 
                : projects.filter(project => project.sdg_id == sdgId);

            renderProjects(filteredProjects);
        } catch (error) {
            projectsContainer.innerHTML = `<p class="no-projects">Error loading projects. Please try again later.</p>`;
            console.error("Error fetching projects:", error);
        }
    });

    function renderProjects(projects) {
        if (!projects || projects.length === 0) {
            projectsContainer.innerHTML = `<p class="no-projects">No projects found for this SDG.</p>`;
            return;
        }

        const html = projects.map(project => {
            const imageHTML = project.image 
                ? `<div class="project-image"><img src="../uploads/project_images/${project.image}" alt="${project.title}"></div>` 
                : "";

            const fileHTML = project.file 
                ? `<a href="../uploads/project_files/${project.file}" download class="download-btn">Download File</a>` 
                : "";

            return `
                <div class="project-card">
                    <div class="project-header">
                        <h3>${project.title}</h3>
                        <span class="project-sdg">${project.sdg_name || 'N/A'}</span>
                    </div>
                    ${imageHTML}
                    <p class="project-description">${project.description.replace(/\n/g, "<br>")}</p>
                    <div class="project-footer">
                        <span>By: ${project.full_name}</span>
                        ${fileHTML}
                    </div>
                </div>
            `;
        }).join("");

        projectsContainer.innerHTML = html;
    }
});
