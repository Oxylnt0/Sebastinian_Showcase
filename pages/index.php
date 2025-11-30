<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Welcome to Sebastinian Showcase</h1>
        <?php if(isset($_SESSION['username'])): ?>
            <p>Hello, <?php echo $_SESSION['username']; ?> | <a href="dashboard.php">Dashboard</a> | <a href="logout.php">Logout</a></p>
        <?php else: ?>
            <p><a href="login.php">Login</a> | <a href="register.php">Register</a></p>
        <?php endif; ?>
    </header>

    <main>
        <h2>Recent Projects</h2>
        <div id="projectsContainer"></div>
    </main>

    <script>
    async function loadProjects() {
        const res = await fetch('../api/projects/get_projects.php');
        const projects = await res.json();
        const container = document.getElementById('projectsContainer');
        container.innerHTML = '';

        projects.slice(0,5).forEach(p => {  // show latest 5 projects
            const div = document.createElement('div');
            div.innerHTML = `
                <h3>${p.title}</h3>
                <p>By: ${p.full_name}</p>
                <p>SDG: ${p.sdg_name || 'None'}</p>
                <p>Status: ${p.status}</p>
                <a href="project.php?id=${p.project_id}">View Details</a>
            `;
            container.appendChild(div);
        });
    }

    loadProjects();
    </script>
</body>
</html>
