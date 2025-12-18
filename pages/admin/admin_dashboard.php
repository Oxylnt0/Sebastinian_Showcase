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

<div class="admin-dashboard-container full-width">

    <div class="dashboard-top-panel">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p class="dashboard-subtitle">Overview of Research & Users</p>
        </div>

        <div class="summary-cards">
            <div class="card total-projects"><h3>Total Research</h3><p><?= $stats['total'] ?></p></div>
            <div class="card approved-projects"><h3>Approved</h3><p><?= $stats['approved'] ?></p></div>
            <div class="card pending-projects"><h3>Pending</h3><p><?= $stats['pending'] ?></p></div>
            <div class="card rejected-projects"><h3>Rejected</h3><p><?= $stats['rejected'] ?></p></div>
            <div class="card students-count"><h3>Students</h3><p><?= $stats['students'] ?></p></div>
            <div class="card admins-count"><h3>Admins</h3><p><?= $stats['admins'] ?></p></div>
        </div>
    </div>

    <nav class="tabs-nav">
        <button class="tab-btn active" data-tab="dashboard">Dashboard Overview</button>
        <button class="tab-btn" data-tab="manage-admins">Manage Admins</button>
        <button class="tab-btn" data-tab="projects">Research Archive</button>
    </nav>

    <div class="tabs-content">

        <section class="tab-pane active" id="dashboard">
            <div class="dashboard-main-grid">
                
                <?php if($admin_profile): ?>
                <div class="grid-item admin-card-box">
                    <?php 
                        $profile_path = !empty($admin_profile['profile_image']) 
                            ? "../../uploads/profile_images/" . htmlspecialchars($admin_profile['profile_image']) 
                            : "../../uploads/profile_images/default.png"; 
                    ?>
                    <div class="admin-profile-card">
                        <img src="<?= $profile_path ?>" alt="Profile" class="profile-pic">
                        <h3><?= htmlspecialchars($admin_profile['full_name']) ?></h3>
                        <p class="admin-email"><?= htmlspecialchars($admin_profile['email']) ?></p>
                        <div class="admin-stats-mini">
                            <span title="Approvals">✅ <?= $admin_profile['approved_count'] ?></span>
                            <span title="Rejections">❌ <?= $admin_profile['rejected_count'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid-item submissions-box">
                    <h2>Recent Submissions</h2>
                    <div class="table-wrapper">
                        <table class="projects-table mini-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_projects as $proj): ?>
                                <tr>
                                    <td><?= htmlspecialchars(substr($proj['title'], 0, 25)) ?>...</td>
                                    <td><span class="status <?= $proj['status'] ?>"><?= ucfirst($proj['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid-item activity-box">
                    <h2>Recent Activity</h2>
                    <ul class="activity-feed">
                        <?php foreach($recent_activity as $act): ?>
                            <li>
                                <strong><?= htmlspecialchars($act['name']) ?></strong>
                                <small><?= date('M d, H:i', strtotime($act['date'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="grid-item top-researchers-box">
                    <h2>Top Researchers</h2>
                    <ul class="top-list">
                        <?php $rank=1; foreach($top_students as $student): ?>
                            <li>
                                <span>#<?= $rank++ ?> <?= htmlspecialchars($student['full_name']) ?></span>
                                <span class="badge"><?= $student['project_count'] ?> Research</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="grid-item chart-box">
                    <canvas id="researchChart"></canvas>
                </div>

                <div class="grid-item chart-box trend-wide">
                    <canvas id="trendChart"></canvas>
                </div>

            </div>
        </section>

        <section class="tab-pane" id="manage-admins">
            <div class="full-width-content">
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
                            <input type="email" name="email" id="email" placeholder="name@sscr.edu" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Access Password</label>
                            <input type="password" name="password" id="password" placeholder="Enter secure password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                        </div>
                        <button type="submit" class="comment-btn">Grant Access</button>
                    </form>
                </div>
                <div id="admins-container"><p>Fetching administrative staff...</p></div>
            </div>
        </section>

        <section class="tab-pane" id="projects">
            <div class="full-width-content">
                <h2>Research & Thesis Management</h2>
                <input type="text" id="projectsSearch" class="projects-search" placeholder="Search research by title or author...">
                <div id="projects-container">
                    <p>Accessing archive...</p>
                </div>
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
// Research Status Pie Chart (Theme: Red & Gold)
const ctx = document.getElementById('researchChart').getContext('2d');
const researchChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            data: [<?= $stats['approved'] ?>, <?= $stats['pending'] ?>, <?= $stats['rejected'] ?>],
            backgroundColor: ['#28a745','#D4AF37','#800000'], // Green, Gold, Red
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: { 
        responsive: true, 
        plugins: { 
            legend: { position: 'bottom' },
            title: { display: true, text: 'Thesis Status Distribution', color: '#800000' }
        } 
    }
});

// Thesis Submission Trends (Theme: Red & Gold)
const trendCtx = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: [<?= implode(',', range(1,12)) ?>].map(m => new Date(0, m-1).toLocaleString('default', { month: 'short' })),
        datasets: [
            {
                label: 'Approved Thesis',
                data: [<?= implode(',', array_map(fn($m)=>$monthly_status[$m]['approved'], range(1,12))) ?>],
                borderColor: '#D4AF37', // Gold
                backgroundColor: 'rgba(212, 175, 55, 0.1)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'Rejected Submissions',
                data: [<?= implode(',', array_map(fn($m)=>$monthly_status[$m]['rejected'], range(1,12))) ?>],
                borderColor: '#800000', // Red
                backgroundColor: 'rgba(128, 0, 0, 0.1)',
                fill: true,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { 
            legend: { position: 'bottom' },
            title: { display: true, text: 'Monthly Thesis Approval Trends', color: '#800000' }
        },
        scales: { y: { beginAtZero: true, stepSize: 1 } }
    }
});
</script>