<?php
// ===============================================
// admin_dashboard.php - Sebastinian Showcase Admin Panel
// ===============================================

session_start();
// admin_dashboard.php
require_once("../../api/config/db.php");
require_once("../../api/utils/auth_check.php");
require_once("../../api/utils/response.php");


// Only admins can access
auth_check(['admin']);

$conn = (new Database())->connect();

// ----------------------------
// Fetch basic stats for dashboard overview
// ----------------------------
$sql_stats = "SELECT status, COUNT(*) AS count FROM projects GROUP BY status";
$result_stats = $conn->query($sql_stats);
$stats = ['approved'=>0,'pending'=>0,'rejected'=>0,'total'=>0];
if ($result_stats) {
    while ($row = $result_stats->fetch_assoc()) {
        $status = $row['status'];
        $stats[$status] = (int)$row['count'];
        $stats['total'] += (int)$row['count'];
    }
}

// Recent submissions (latest 5 projects)
$sql_recent = "
    SELECT p.project_id, p.title, p.status, p.date_submitted, u.full_name AS student_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    ORDER BY p.date_submitted DESC
    LIMIT 5
";
$result_recent = $conn->query($sql_recent);
$recent_projects = $result_recent ? $result_recent->fetch_all(MYSQLI_ASSOC) : [];
?>

<?php include("../header.php"); ?>

<div class="admin-dashboard-container">
    <h1>Admin Panel</h1>

    <!-- Tabs Navigation -->
    <div class="tabs-nav">
        <button class="tab-btn active" data-tab="dashboard">Dashboard</button>
        <button class="tab-btn" data-tab="manage-admins">Manage Admins</button>
        <button class="tab-btn" data-tab="projects">Projects</button>
    </div>

    <!-- Tabs Content -->
    <div class="tabs-content">

        <!-- Dashboard Overview -->
        <div class="tab-pane active" id="dashboard">
            <h2>Dashboard Overview</h2>

            <div class="summary-cards">
                <div class="card total-projects"><h3>Total Projects</h3><p><?= $stats['total'] ?></p></div>
                <div class="card approved-projects"><h3>Approved</h3><p><?= $stats['approved'] ?></p></div>
                <div class="card pending-projects"><h3>Pending</h3><p><?= $stats['pending'] ?></p></div>
                <div class="card rejected-projects"><h3>Rejected</h3><p><?= $stats['rejected'] ?></p></div>
            </div>

            <div class="recent-projects">
                <h3>Recent Submissions</h3>
                <?php if(empty($recent_projects)): ?>
                    <p>No recent projects submitted.</p>
                <?php else: ?>
                    <table class="projects-table">
                        <thead>
                            <tr><th>Title</th><th>Student</th><th>Status</th><th>Date Submitted</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_projects as $proj): ?>
                                <tr>
                                    <td><?= htmlspecialchars($proj['title']) ?></td>
                                    <td><?= htmlspecialchars($proj['student_name']) ?></td>
                                    <td class="status <?= $proj['status'] ?>"><?= ucfirst($proj['status']) ?></td>
                                    <td><?= date("M d, Y H:i", strtotime($proj['date_submitted'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Manage Admins -->
        <div class="tab-pane" id="manage-admins">
            <h2>Manage Admins</h2>
            <div id="admins-container">
                <!-- AJAX will populate the admin table + add form -->
            </div>
        </div>

        <!-- Projects Management -->
        <div class="tab-pane" id="projects">
            <h2>Project Management</h2>
            <div id="projects-container">
                <!-- AJAX will populate project table + search/filter -->
            </div>
        </div>

    </div>
</div>

<!-- Student Profile Modal -->
<div id="studentModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="studentProfileContainer">
            <!-- AJAX will populate student profile + projects -->
        </div>
    </div>
</div>

<?php include("../footer.php"); ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // -----------------------------
    // Tabs functionality
    // -----------------------------
    const tabs = document.querySelectorAll(".tab-btn");
    const panes = document.querySelectorAll(".tab-pane");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(t => t.classList.remove("active"));
            panes.forEach(p => p.classList.remove("active"));

            tab.classList.add("active");
            document.getElementById(tab.dataset.tab).classList.add("active");

            // Load content dynamically for AJAX tabs
            if(tab.dataset.tab === 'manage-admins') fetchAdmins();
            if(tab.dataset.tab === 'projects') fetchProjects();
        });
    });

    // -----------------------------
    // Placeholder AJAX functions
    // -----------------------------
    const adminsContainer = document.getElementById("admins-container");
    window.fetchAdmins = async () => {
        adminsContainer.innerHTML = "<p>Loading admins...</p>";
        try {
            const res = await fetch("/Sebastinian_Showcase/api/admin/get_admins.php");
            const data = await res.json();
            if(data.status === "success") {
                let html = `<table>
                    <thead><tr><th>Username</th><th>Email</th><th>Created</th></tr></thead><tbody>`;
                data.data.forEach(a => {
                    html += `<tr>
                        <td>${a.username}</td>
                        <td>${a.email}</td>
                        <td>${new Date(a.date_created).toLocaleDateString()}</td>
                    </tr>`;
                });
                html += "</tbody></table>";
                adminsContainer.innerHTML = html;
            } else {
                adminsContainer.innerHTML = `<p>${data.message}</p>`;
            }
        } catch(e) {
            adminsContainer.innerHTML = "<p>Error loading admins.</p>";
        }
    }

    const projectsContainer = document.getElementById("projects-container");
    window.fetchProjects = async () => {
        projectsContainer.innerHTML = "<p>Loading projects...</p>";
        try {
            const res = await fetch("/Sebastinian_Showcase/api/admin/get_projects.php")
            const data = await res.json();
            if(data.status === "success") {
                let html = `<table>
                    <thead><tr><th>Title</th><th>Student</th><th>Status</th><th>Date Submitted</th></tr></thead><tbody>`;
                data.data.forEach(p => {
                    html += `<tr>
                        <td>${p.title}</td>
                        <td>${p.student_name}</td>
                        <td class="status ${p.status}">${p.status}</td>
                        <td>${new Date(p.date_submitted).toLocaleString()}</td>
                    </tr>`;
                });
                html += "</tbody></table>";
                projectsContainer.innerHTML = html;
            } else {
                projectsContainer.innerHTML = `<p>${data.message}</p>`;
            }
        } catch(e) {
            projectsContainer.innerHTML = "<p>Error loading projects.</p>";
        }
    }
});
</script>
