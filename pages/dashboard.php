<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$isAdmin = $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sebastinian Showcase</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff5e6;
            margin: 0;
            padding: 0;
        }

        header {
            background: #8B0000;
            color: #ffd700;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
        }

        header .logout-btn {
            background: #ffd700;
            color: #8B0000;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        header .logout-btn:hover {
            background: #e6c200;
        }

        main {
            padding: 30px;
        }

        .welcome {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #8B0000;
        }

        .actions {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .actions button, .actions select {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .actions button {
            background: #8B0000;
            color: #ffd700;
            border: none;
        }

        .actions button:hover {
            background: #a30000;
        }

        .actions select {
            border: 1px solid #d4af37;
        }

        .projects-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .project-card {
            background: #fff;
            border: 1px solid #d4af37;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .project-card img {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .project-card h3 {
            margin: 0 0 5px 0;
            color: #8B0000;
        }

        .project-card p {
            flex: 1;
            margin: 5px 0;
        }

        .project-card .meta {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 5px;
        }

        .project-card .status {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 6px;
            text-align: center;
            width: fit-content;
            margin-top: 5px;
        }

        .status.pending { background: #ffd700; color: #8B0000; }
        .status.approved { background: #008000; color: #fff; }
        .status.rejected { background: #8B0000; color: #fff; }

        .admin-actions button {
            margin-right: 5px;
            padding: 4px 8px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Sebastinian Showcase</h1>
        <button class="logout-btn" id="logoutBtn">Logout</button>
    </header>
    <main>
        <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
        <div class="actions">
            <button id="uploadBtn">Upload New Project</button>
            <select id="sdgFilter">
                <option value="">All SDGs</option>
                <option value="1">SDG 4 – Quality Education</option>
                <option value="2">SDG 9 – Industry, Innovation, and Infrastructure</option>
                <option value="3">SDG 11 – Sustainable Cities & Communities</option>
                <option value="4">SDG 13 – Climate Action</option>
            </select>
        </div>
        <div class="projects-container" id="projectsContainer">
            <!-- Projects will load here dynamically -->
        </div>
    </main>

    <script>
        const projectsContainer = document.getElementById('projectsContainer');
        const sdgFilter = document.getElementById('sdgFilter');
        const logoutBtn = document.getElementById('logoutBtn');
        const uploadBtn = document.getElementById('uploadBtn');
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

        async function fetchProjects() {
            projectsContainer.innerHTML = 'Loading projects...';
            let url = `../api/projects/get_projects.php`;
            if (sdgFilter.value) {
                url += `?sdg_id=${sdgFilter.value}`;
            }

            try {
                const res = await fetch(url);
                const data = await res.json();

                if (data.status === 'success') {
                    projectsContainer.innerHTML = '';
                    if (data.data.length === 0) {
                        projectsContainer.innerHTML = '<p>No projects found.</p>';
                        return;
                    }

                    data.data.forEach(project => {
                        const card = document.createElement('div');
                        card.className = 'project-card';

                        let adminButtons = '';
                        if (isAdmin && project.status === 'pending') {
                            adminButtons = `
                                <div class="admin-actions">
                                    <button onclick="updateStatus(${project.project_id}, 'approved')">Approve</button>
                                    <button onclick="updateStatus(${project.project_id}, 'rejected')">Reject</button>
                                </div>
                            `;
                        }

                        card.innerHTML = `
                            ${project.image ? `<img src="${project.image}" alt="${project.title}">` : ''}
                            <h3>${project.title}</h3>
                            <div class="meta">By: ${project.user.full_name}</div>
                            <div class="meta">SDG: ${project.sdg?.sdg_name || 'N/A'}</div>
                            <div class="meta">Submitted: ${new Date(project.date_submitted).toLocaleDateString()}</div>
                            <p>${project.description}</p>
                            <div class="status ${project.status}">${project.status.charAt(0).toUpperCase() + project.status.slice(1)}</div>
                            ${adminButtons}
                        `;
                        projectsContainer.appendChild(card);
                    });
                } else {
                    projectsContainer.innerHTML = `<p>Error: ${data.message}</p>`;
                }
            } catch (err) {
                projectsContainer.innerHTML = `<p>Network error. Please try again.</p>`;
            }
        }

        async function updateStatus(projectId, status) {
            const remarks = prompt(`Add remarks (optional) for ${status} action:`) || '';
            try {
                const formData = new FormData();
                formData.append('project_id', projectId);
                formData.append('status', status);
                formData.append('remarks', remarks);

                const res = await fetch('../api/projects/approve_project.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.status === 'success') {
                    alert(data.message);
                    fetchProjects();
                } else {
                    alert("Error: " + data.message);
                }
            } catch (err) {
                alert("Network error. Please try again.");
            }
        }

        sdgFilter.addEventListener('change', fetchProjects);
        uploadBtn.addEventListener('click', () => window.location.href = 'project.php');
        logoutBtn.addEventListener('click', async () => {
            await fetch('../api/auth/logout.php');
            window.location.href = 'login.php';
        });

        fetchProjects();
    </script>
</body>
</html>
