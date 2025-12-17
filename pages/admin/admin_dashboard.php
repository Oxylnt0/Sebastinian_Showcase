<?php 
// ===============================================
// pages/admin/admin_dashboard.php
// ===============================================

session_start();

// Adjust paths for the "admin" subdirectory
require_once("../../api/config/db.php");
require_once("../../api/utils/auth_check.php");
require_once("../../api/utils/response.php");

// Only admins can access
auth_check(['admin']);

$conn = (new Database())->connect();

// ----------------------------
// Fetch Dashboard Stats
// ----------------------------
$stats = ['approved'=>0,'pending'=>0,'rejected'=>0,'total'=>0,'students'=>0,'admins'=>0,'active_this_month'=>0];

$sql_stats = "SELECT status, COUNT(*) AS count FROM projects GROUP BY status";
$result_stats = $conn->query($sql_stats);
if ($result_stats) {
    while ($row = $result_stats->fetch_assoc()) {
        $status = $row['status'];
        $stats[$status] = (int)$row['count'];
        $stats['total'] += (int)$row['count'];
    }
}

$sql_students = "SELECT COUNT(*) AS total_students FROM users WHERE role='student'";
$result_students = $conn->query($sql_students);
$stats['students'] = $result_students ? $result_students->fetch_assoc()['total_students'] : 0;

$sql_admins = "SELECT COUNT(*) AS total_admins FROM users WHERE role='admin'";
$result_admins = $conn->query($sql_admins);
$stats['admins'] = $result_admins ? $result_admins->fetch_assoc()['total_admins'] : 0;

$sql_active = "SELECT COUNT(*) AS active_this_month FROM projects WHERE MONTH(date_submitted) = MONTH(CURRENT_DATE()) AND YEAR(date_submitted) = YEAR(CURRENT_DATE())";
$result_active = $conn->query($sql_active);
$stats['active_this_month'] = $result_active ? $result_active->fetch_assoc()['active_this_month'] : 0;

// ----------------------------
// Fetch Recent Research (5 latest)
// ----------------------------
$sql_recent = "
    SELECT p.project_id, p.title, p.status, p.date_submitted, u.full_name AS student_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    ORDER BY p.date_submitted DESC
    LIMIT 5
";
$result_recent = $conn->query($sql_recent);
$recent_projects = $result_recent ? $result_recent->fetch_all(MYSQLI_ASSOC) : [];

// ----------------------------
// Fetch Recent Activity
// ----------------------------
$sql_activity = "
    SELECT 'admin' AS type, username AS name, date_created AS date FROM users WHERE role='admin'
    UNION
    SELECT 'student' AS type, full_name AS name, date_created AS date FROM users WHERE role='student'
    UNION
    SELECT 'research' AS type, CONCAT(title, ' (', status, ')') AS name, date_submitted AS date FROM projects
    ORDER BY date DESC
    LIMIT 5
";
$result_activity = $conn->query($sql_activity);
$recent_activity = $result_activity ? $result_activity->fetch_all(MYSQLI_ASSOC) : [];

// ----------------------------
// Top Researchers (Most Submissions)
// ----------------------------
$sql_top_students = "
    SELECT u.full_name, COUNT(p.project_id) AS project_count
    FROM users u
    JOIN projects p ON u.user_id = p.user_id
    WHERE u.role='student'
    GROUP BY u.user_id
    ORDER BY project_count DESC
    LIMIT 5
";
$result_top_students = $conn->query($sql_top_students);
$top_students = $result_top_students ? $result_top_students->fetch_all(MYSQLI_ASSOC) : [];

// ----------------------------
// Research Approved vs Rejected Trend
// ----------------------------
$sql_monthly_status = "
    SELECT MONTH(date_submitted) AS month, status, COUNT(*) AS count
    FROM projects
    WHERE YEAR(date_submitted) = YEAR(CURRENT_DATE())
    GROUP BY month, status
    ORDER BY month
";
$result_monthly_status = $conn->query($sql_monthly_status);
$monthly_status = [];
if($result_monthly_status){
    while($row = $result_monthly_status->fetch_assoc()){
        $monthly_status[$row['month']][$row['status']] = (int)$row['count'];
    }
}
for($m=1;$m<=12;$m++){
    if(!isset($monthly_status[$m])) $monthly_status[$m] = ['approved'=>0,'rejected'=>0];
    if(!isset($monthly_status[$m]['approved'])) $monthly_status[$m]['approved'] = 0;
    if(!isset($monthly_status[$m]['rejected'])) $monthly_status[$m]['rejected'] = 0;
}

// ----------------------------
// Admin Profile Overview
// ----------------------------
$admin_id = $_SESSION['user_id'];
$sql_admin_profile = "
    SELECT 
        username, full_name, email, date_created, profile_image,
        (SELECT COUNT(*) FROM approvals WHERE approved_by = $admin_id) AS total_approvals,
        (SELECT COUNT(*) FROM approvals WHERE approved_by = $admin_id AND status='approved') AS approved_count,
        (SELECT COUNT(*) FROM approvals WHERE approved_by = $admin_id AND status='rejected') AS rejected_count
    FROM users
    WHERE user_id = $admin_id AND role='admin'
";
$result_admin_profile = $conn->query($sql_admin_profile);
$admin_profile = $result_admin_profile ? $result_admin_profile->fetch_assoc() : null;

?>

<?php include("../header.php"); ?>

<div class="admin-dashboard-container">

    <div class="dashboard-top-panel">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p class="dashboard-subtitle">Overview of Research & Users</p>
        </div>

        <div class="summary-cards">
            <div class="card total-projects"><h3>Total Research</h3><p><?= $stats['total'] ?></p></div>
            <div class="card approved-projects"><h3>Approved Thesis</h3><p><?= $stats['approved'] ?></p></div>
            <div class="card pending-projects"><h3>Pending Review</h3><p><?= $stats['pending'] ?></p></div>
            <div class="card rejected-projects"><h3>Rejected</h3><p><?= $stats['rejected'] ?></p></div>
            <div class="card students-count"><h3>Students</h3><p><?= $stats['students'] ?></p></div>
            <div class="card admins-count"><h3>Admins</h3><p><?= $stats['admins'] ?></p></div>
            <div class="card active-this-month"><h3>New This Month</h3><p><?= $stats['active_this_month'] ?></p></div>
        </div>
    </div>

    <nav class="tabs-nav">
        <button class="tab-btn active" data-tab="dashboard">Dashboard</button>
        <button class="tab-btn" data-tab="manage-admins">Manage Admins</button>
        <button class="tab-btn" data-tab="projects">Research Archive</button>
    </nav>

    <div class="tabs-content">

        <section class="tab-pane active" id="dashboard">

            <?php if($admin_profile): ?>
                <?php 
                    $profile_path = !empty($admin_profile['profile_image']) 
                        ? "../../uploads/profile_images/" . htmlspecialchars($admin_profile['profile_image']) 
                        : "../../uploads/profile_images/default.png"; 
                ?>
            <div class="admin-profile-card">
                <img src="<?= $profile_path ?>" alt="Profile" class="profile-pic">
                <h3><?= htmlspecialchars($admin_profile['full_name']) ?></h3>
                <p>Username: <?= htmlspecialchars($admin_profile['username']) ?></p>
                <p>Email: <?= htmlspecialchars($admin_profile['email']) ?></p>
                <p>Joined: <?= date('M d, Y', strtotime($admin_profile['date_created'])) ?></p>
                <p>Review Actions: <?= $admin_profile['total_approvals'] ?> (✅ <?= $admin_profile['approved_count'] ?> / ❌ <?= $admin_profile['rejected_count'] ?>)</p>
            </div>
            <?php endif; ?>

            <div class="recent-projects">
                <h2>Recent Thesis Submissions</h2>
                <?php if(empty($recent_projects)): ?>
                    <p>No recent research submitted.</p>
                <?php else: ?>
                    <table class="projects-table">
                        <thead>
                            <tr>
                                <th>Thesis Title</th>
                                <th>Researcher</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_projects as $proj): ?>
                                <tr>
                                    <td><?= htmlspecialchars($proj['title']) ?></td>
                                    <td><?= htmlspecialchars($proj['student_name']) ?></td>
                                    <td class="status-cell">
                                        <span class="status <?= $proj['status'] ?>"><?= ucfirst($proj['status']) ?></span>
                                    </td>
                                    <td><?= date("M d, Y H:i", strtotime($proj['date_submitted'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h2>Recent Archive Activity</h2>
                <?php if(empty($recent_activity)): ?>
                    <p>No recent activity.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach($recent_activity as $act): ?>
                            <li>
                                <?php
                                    $icon = $act['type'] === 'admin' ? '👤' : ($act['type']==='student' ? '🎓' : '📚');
                                    echo "$icon <strong>".htmlspecialchars($act['name'])."</strong> <em>(".date('M d, Y H:i', strtotime($act['date'])).")</em>";
                                ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="top-students">
                <h2>Top Researchers (Most Submissions)</h2>
                <?php if(empty($top_students)): ?>
                    <p>No research submissions yet.</p>
                <?php else: ?>
                    <table class="projects-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student Researcher</th>
                                <th>Thesis Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank=1; foreach($top_students as $student): ?>
                                <tr>
                                    <td><?= $rank++ ?></td>
                                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                                    <td><?= $student['project_count'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="dashboard-charts">
                <canvas id="researchChart" height="150"></canvas>
                <canvas id="trendChart" height="150"></canvas>
            </div>

        </section>

        <section class="tab-pane" id="manage-admins">
            <h2>Administrative Management</h2>
            <div class="add-admin-form">
                <h3>Authorize New Admin</h3>
                <form id="addAdminForm" autocomplete="off">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" placeholder="Enter username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Institutional Email</label>
                        <input type="email" name="email" id="email" placeholder="Enter email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Access Password</label>
                        <input type="password" name="password" id="password" placeholder="Enter password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Access Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                    </div>
                    <button type="submit" class="btn-primary">Grant Access</button>
                </form>
            </div>
            <div id="admins-container"><p>Fetching administrative staff...</p></div>
        </section>

        <section class="tab-pane" id="projects">
            <h2>Research & Thesis Management</h2>
            <input type="text" id="projectsSearch" class="projects-search" placeholder="Search research by title or author...">
            <div id="projects-container">
                <p>Accessing archive...</p>
            </div>
        </section>

    </div>
</div>

<div id="studentModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="studentProfileContainer"></div>
    </div>
</div>
<div id="dashboardFeedback" class="feedback"></div>

<?php include("../footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="admin_dashboard.js"></script>

<script>
// Research Status Pie Chart
const ctx = document.getElementById('researchChart').getContext('2d');
const researchChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            data: [<?= $stats['approved'] ?>, <?= $stats['pending'] ?>, <?= $stats['rejected'] ?>],
            backgroundColor: ['#4CAF50','#FFB300','#D32F2F'],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: { 
        responsive: true, 
        plugins: { 
            legend: { position: 'bottom' },
            title: { display: true, text: 'Thesis Status Distribution' }
        } 
    }
});

// Thesis Submission Trends
const trendCtx = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: [<?= implode(',', range(1,12)) ?>].map(m => new Date(0, m-1).toLocaleString('default', { month: 'short' })),
        datasets: [
            {
                label: 'Approved Thesis',
                data: [<?= implode(',', array_map(fn($m)=>$monthly_status[$m]['approved'], range(1,12))) ?>],
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80,0.1)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'Rejected Submissions',
                data: [<?= implode(',', array_map(fn($m)=>$monthly_status[$m]['rejected'], range(1,12))) ?>],
                borderColor: '#D32F2F',
                backgroundColor: 'rgba(211, 47, 47,0.1)',
                fill: true,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { 
            legend: { position: 'bottom' },
            title: { display: true, text: 'Monthly Thesis Approval Trends' }
        },
        scales: { y: { beginAtZero: true, stepSize: 1 } }
    }
});
</script>