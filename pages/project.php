<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Details - Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Project Details</h2>
    <div id="projectContainer"></div>

    <script>
    const urlParams = new URLSearchParams(window.location.search);
    const projectId = urlParams.get('id');

    async function loadProject() {
        const res = await fetch('../api/projects/get_projects.php');
        const projects = await res.json();
        const project = projects.find(p => p.project_id == projectId);
        if(!project) {
            document.getElementById('projectContainer').innerText = 'Project not found';
            return;
        }

        const div = document.getElementById('projectContainer');
        div.innerHTML = `
            <h3>${project.title}</h3>
            <p>By: ${project.full_name}</p>
            <p>SDG: ${project.sdg_name || 'None'}</p>
            <p>Status: ${project.status}</p>
            <p>${project.description}</p>
            ${project.image ? `<img src="../uploads/project_images/${project.image}" width="200">` : ''}
            ${project.file ? `<a href="../uploads/project_files/${project.file}" target="_blank">Download File</a>` : ''}
        `;
    }

    loadProject();
    </script>
</body>
</html>
