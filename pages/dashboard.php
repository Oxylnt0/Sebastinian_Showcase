<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Sebastinian Showcase</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    /* Reset & Base */
    * { box-sizing: border-box; margin:0; padding:0; }
    body { font-family: 'Arial', sans-serif; background: #f5f5f5; color: #333; min-height: 100vh; }

    header {
        background: #007bff;
        color: #fff;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    header h1 { font-size: 24px; margin-bottom: 5px; }
    header nav a { color: #fff; text-decoration: none; margin-left: 15px; font-weight: bold; }
    header nav a:hover { text-decoration: underline; }

    main { padding: 20px 30px; max-width: 1200px; margin: auto; }

    h2 { margin-bottom: 15px; color: #007bff; }

    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .project-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .project-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .project-card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
    }

    .project-content {
        padding: 15px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .project-content h3 { font-size: 18px; margin-bottom: 5px; color: #007bff; }
    .project-content p { font-size: 14px; margin-bottom: 8px; }

    .badges {
        margin-bottom: 10px;
    }
    .badge {
        display: inline-block;
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 5px;
        color: #fff;
        margin-right: 5px;
    }

    /* SDG Colors */
    .sdg-1 { background-color: #007bff; } /* SDG 4 – Blue */
    .sdg-2 { background-color: #6f42c1; } /* SDG 9 – Purple */
    .sdg-3 { background-color: #20c997; } /* SDG 11 – Green */
    .sdg-4 { background-color: #fd7e14; } /* SDG 13 – Orange */

    /* Status Colors */
    .status-approved { background-color: #28a745; }
    .status-pending { background-color: #ffc107; color: #212529; }
    .status-rejected { background-color: #dc3545; }

    .btn-view {
        margin-top: auto;
        text-align: center;
        background: #007bff;
        color: #fff;
        padding: 10px 0;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s;
    }
    .btn-view:hover { background: #0056b3; }

    /* Responsive */
    @media(max-width:480px){
        header { flex-direction: column; align-items: flex-start; }
        header nav { margin-top: 5px; }
    }
</style>
</head>
<body>

<header>
    <h1>Dashboard - Sebastinian Showcase</h1>
    <nav>
        <a href="project.php">Upload Project</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h2>All Projects</h2>
    <div class="projects-grid" id="projectsContainer">
        <p>Loading projects...</p>
    </div>
</main>

<script>
async function loadProjects() {
    const container = document.getElementById('projectsContainer');
    try {
        const res = await fetch('../api/projects/get_projects.php');
        const projects = await res.json();

        container.innerHTML = '';
        if (!projects || projects.length === 0) {
            container.innerHTML = '<p>No projects found.</p>';
            return;
        }

        projects.forEach(p => {
            // SDG badge class
            let sdgClass = '';
            if (p.sdg_id == 1) sdgClass = 'sdg-1';
            else if (p.sdg_id == 2) sdgClass = 'sdg-2';
            else if (p.sdg_id == 3) sdgClass = 'sdg-3';
            else if (p.sdg_id == 4) sdgClass = 'sdg-4';

            // Status class
            let statusClass = '';
            if (p.status === 'approved') statusClass = 'status-approved';
            else if (p.status === 'pending') statusClass = 'status-pending';
            else if (p.status === 'rejected') statusClass = 'status-rejected';

            const card = document.createElement('div');
            card.className = 'project-card';
            card.innerHTML = `
                ${p.image ? `<img src="../uploads/project_images/${p.image}" alt="${p.title}">` : ''}
                <div class="project-content">
                    <h3>${p.title}</h3>
                    <p><strong>By:</strong> ${p.full_name}</p>
                    <div class="badges">
                        ${p.sdg_name ? `<span class="badge ${sdgClass}">${p.sdg_name}</span>` : ''}
                        <span class="badge ${statusClass}">${p.status.charAt(0).toUpperCase() + p.status.slice(1)}</span>
                    </div>
                    <a class="btn-view" href="project.php?id=${p.project_id}">View Details</a>
                </div>
            `;
            container.appendChild(card);
        });
    } catch (err) {
        console.error('Error loading projects:', err);
        container.innerHTML = '<p>Error loading projects.</p>';
    }
}

loadProjects();
</script>

</body>
</html>
