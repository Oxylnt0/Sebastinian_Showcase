<?php
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/auth_check.php"); // Utility to check if admin is logged in
require_once("../api/utils/response.php");

auth_check('admin'); // Only admins can access

$conn = (new Database())->connect();

// Fetch all projects with user and SDG info
$sql = "
    SELECT 
        p.project_id,
        p.title,
        p.description,
        p.status,
        p.date_submitted,
        u.full_name AS student_name,
        s.sdg_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
    ORDER BY p.date_submitted DESC
";

$result = $conn->query($sql);

$projects = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
} else {
    $projects = [];
}

?>

<?php include("header.php"); ?>

<div class="admin-dashboard-container">
    <h1>Admin Dashboard</h1>
    
    <div class="summary-cards">
        <div class="card total-projects">
            <h3>Total Projects</h3>
            <p><?php echo count($projects); ?></p>
        </div>
        <div class="card approved-projects">
            <h3>Approved</h3>
            <p><?php 
                echo count(array_filter($projects, fn($p) => $p['status'] === 'approved')); 
            ?></p>
        </div>
        <div class="card pending-projects">
            <h3>Pending</h3>
            <p><?php 
                echo count(array_filter($projects, fn($p) => $p['status'] === 'pending')); 
            ?></p>
        </div>
        <div class="card rejected-projects">
            <h3>Rejected</h3>
            <p><?php 
                echo count(array_filter($projects, fn($p) => $p['status'] === 'rejected')); 
            ?></p>
        </div>
    </div>

    <div class="projects-table-container">
        <h2>Project Submissions</h2>
        <?php if(count($projects) === 0): ?>
            <p class="no-projects">No projects submitted yet.</p>
        <?php else: ?>
            <table class="projects-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Student</th>
                        <th>SDG</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($projects as $proj): ?>
                        <tr data-project-id="<?php echo $proj['project_id']; ?>">
                            <td><?php echo htmlspecialchars($proj['title']); ?></td>
                            <td><?php echo htmlspecialchars($proj['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($proj['sdg_name']); ?></td>
                            <td class="status <?php echo $proj['status']; ?>"><?php echo ucfirst($proj['status']); ?></td>
                            <td><?php echo date("M d, Y H:i", strtotime($proj['date_submitted'])); ?></td>
                            <td class="actions">
                                <?php if($proj['status'] === 'pending'): ?>
                                    <button class="approve-btn" data-status="approved">Approve</button>
                                    <button class="reject-btn" data-status="rejected">Reject</button>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include("footer.php"); ?>
