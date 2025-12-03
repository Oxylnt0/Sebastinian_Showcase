<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Details - Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/project.css">
    <style>
        /* Base Reset */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Arial', sans-serif; background: #f5f5f5; color: #333; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }

        /* Card Styling */
        .project-card {
            background: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            max-width: 720px;
            width: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .project-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.15); }

        .project-card h2 {
            color: #007bff;
            margin-bottom: 15px;
            font-size: 24px;
        }
        .project-card p {
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        /* Badges */
        .badges { margin: 15px 0; }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            margin-right: 5px;
        }
        .badge.sdg { background-color: #17a2b8; }         /* SDG Info */
        .badge.approved { background-color: #28a745; }
        .badge.pending { background-color: #ffc107; color: #212529; }
        .badge.rejected { background-color: #dc3545; }

        /* Images */
        .project-card img {
            max-width: 100%;
            border-radius: 8px;
            margin: 15px 0;
        }

        /* Download Button */
        a.download-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s, transform 0.2s;
        }
        a.download-btn:hover { background: #0056b3; transform: translateY(-2px); }

        /* Responsive */
        @media(max-width: 480px){
            .project-card { padding: 20px 15px; }
            .project-card h2 { font-size: 20px; }
        }
    </style>
</head>
<body>

<div class="project-card" id="projectContainer">
    Loading project...
</div>

<script>
const container = document.getElementById('projectContainer');
const projectId = new URLSearchParams(window.location.search).get('id');

async function loadProject() {
    try {
        const res = await fetch('../api/projects/get_projects.php');
        const projects = await res.json();
        const project = projects.find(p => p.project_id == projectId);

        if (!project) {
            container.innerHTML = '<p>Project not found.</p>';
            return;
        }

        const statusClass = project.status.toLowerCase();
        const sdgBadge = project.sdg_name ? `<span class="badge sdg">${project.sdg_name}</span>` : '';
        const statusBadge = `<span class="badge ${statusClass}">${project.status.charAt(0).toUpperCase() + project.status.slice(1)}</span>`;

        container.innerHTML = `
            <h2>${project.title}</h2>
            <p><strong>By:</strong> ${project.full_name}</p>
            <div class="badges">${sdgBadge} ${statusBadge}</div>
            <p>${project.description}</p>
            ${project.image ? `<img src="../uploads/project_images/${project.image}" alt="${project.title} Image">` : ''}
            ${project.file ? `<a href="../uploads/project_files/${project.file}" class="download-btn" target="_blank">Download File</a>` : ''}
        `;
    } catch (err) {
        container.innerHTML = '<p>An error occurred while loading the project.</p>';
        console.error(err);
    }
}

loadProject();
</script>

</body>
</html>
