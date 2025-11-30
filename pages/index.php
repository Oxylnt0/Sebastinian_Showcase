<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Basic Reset */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Arial', sans-serif; background: #f5f5f5; color: #333; }

        header {
            background: #007bff;
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 { font-size: 22px; }
        header nav a { color: #fff; text-decoration: none; margin-left: 15px; }
        header nav a:hover { text-decoration: underline; }

        main { padding: 20px 30px; }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .project-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s;
        }
        .project-card:hover { transform: translateY(-5px); }

        .project-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .project-content { padding: 15px; flex: 1; display: flex; flex-direction: column; }

        .project-content h3 { font-size: 18px; margin-bottom: 5px; color: #007bff; }
        .project-content p { font-size: 14px; margin-bottom: 5px; }

        .badges {
            margin: 10px 0;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 12px;
            border-radius: 4px;
            color: #fff;
            margin-right: 5px;
        }
        .sdg-1 { background-color: #007bff; } /* SDG 4 */
        .sdg-2 { background-color: #6f42c1; } /* SDG 9 */
        .sdg-3 { background-color: #20c997; } /* SDG 11 */
        .sdg-4 { background-color: #fd7e14; } /* SDG 13 */

        .status-approved { background-color: #28a745; }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-rejected { background-color: #dc3545; }

        .btn-view {
            margin-top: auto;
            text-align: center;
            background: #007bff;
            color: #fff;
            padding: 8px 0;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-view:hover { background: #0056b3; }
    </style>
</head>
<body>

<header>
    <h1>Sebastinian Showcase</h1>
    <nav>
        <?php if(isset($_SESSION['username'])): ?>
            <span>Hello, <?php echo $_SESSION['username']; ?></span>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <h2>Latest Projects</h2>
    <div class="projects-grid" id="projectsContainer">
        <!-- Projects will load here -->
    </div>
</main>

<script>
async function loadProjects() {
    try {
        const res = await fetch('../api/projects/get_projects.php');
        const projects = await res.json();
        const container = document.getElementById('projectsContainer');
        container.innerHTML = '';

        if (projects.length === 0) {
            container.innerHTML = '<p>No projects found.</p>';
            return;
        }

        // Show only latest 5 projects
        projects.slice(0,5).forEach(p => {
            // Determine SDG class
            let sdgClass = '';
            if (p.sdg_id == 1) sdgClass = 'sdg-1';
            else if (p.sdg_id == 2) sdgClass = 'sdg-2';
            else if (p.sdg_id == 3) sdgClass = 'sdg-3';
            else if (p.sdg_id == 4) sdgClass = 'sdg-4';

            // Determine status class
            let statusClass = '';
            if (p.status === 'approved') statusClass = 'status-approved';
            else if (p.status === 'pending') statusClass = 'status-pending';
            else if (p.status === 'rejected') statusClass = 'status-rejected';

            const div = document.createElement('div');
            div.className = 'project-card';
            div.innerHTML = `
                ${p.image ? `<img src="../uploads/project_images/${p.image}" alt="${p.title}">` : ''}
                <div class="project-content">
                    <h3>${p.title}</h3>
                    <p>By: ${p.full_name}</p>
                    <div class="badges">
                        ${p.sdg_name ? `<span class="badge ${sdgClass}">${p.sdg_name}</span>` : ''}
                        <span class="badge ${statusClass}">${p.status.charAt(0).toUpperCase() + p.status.slice(1)}</span>
                    </div>
                    <a class="btn-view" href="pages/project.php?id=${p.project_id}">View Details</a>
                </div>
            `;
            container.appendChild(div);
        });
    } catch (err) {
        console.error('Error loading projects:', err);
        document.getElementById('projectsContainer').innerHTML = '<p>Error loading projects.</p>';
    }
}

loadProjects();
</script>

</body>
</html>