<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Dashboard</h2>
    <a href="project.php">Upload Project</a>
    <a href="logout.php">Logout</a>

    <div id="projectsContainer"></div>

    <script>
    async function loadProjects() {
        const res = await fetch('../api/projects/get_projects.php');
        const projects = await res.json();
        const container = document.getElementById('projectsContainer');
        container.innerHTML = '';

        projects.forEach(p => {
            const div = document.createElement('div');
            div.innerHTML = `
                <h3>${p.title}</h3>
                <p>By: ${p.full_name}</p>
                <p>SDG: ${p.sdg_name || 'None'}</p>
                <p>Status: ${p.status}</p>
                <a href="project.php?id=${p.project_id}">View</a>
            `;
            container.appendChild(div);
        });
    }

    loadProjects();
    </script>
</body>
</html>
