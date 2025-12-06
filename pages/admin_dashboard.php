<?php
// ===============================================
// admin_dashboard.php - Sebastinian Showcase
// PREMIUM, PRODUCTION-READY, FULL FEATURED
// ===============================================

session_start();
require_once("../api/config/db.php");
require_once("../api/utils/auth_check.php");
require_once("../api/utils/response.php");

// Only admins can access
auth_check(['admin']);

$conn = (new Database())->connect();

// ----------------------------
// Fetch Projects with User & SDG info
// Only pending and approved (rejected are hidden)
// ----------------------------
$sql_projects = "
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
    WHERE p.status != 'rejected'
    ORDER BY p.date_submitted DESC
";
$result_projects = $conn->query($sql_projects);
$projects = $result_projects ? $result_projects->fetch_all(MYSQLI_ASSOC) : [];

// ----------------------------
// Fetch all admins
// ----------------------------
$sql_admins = "SELECT user_id, username, full_name, email, date_created FROM users WHERE role='admin' ORDER BY date_created DESC";
$result_admins = $conn->query($sql_admins);
$admins = $result_admins ? $result_admins->fetch_all(MYSQLI_ASSOC) : [];
?>

<?php include("header.php"); ?>

<div class="admin-dashboard-container">
    <h1>Admin Dashboard</h1>

    <!-- ===================== -->
    <!-- Summary Cards -->
    <!-- ===================== -->
    <div class="summary-cards">
        <div class="card total-projects">
            <h3>Total Projects</h3>
            <p><?= count($projects) ?></p>
        </div>
        <div class="card approved-projects">
            <h3>Approved</h3>
            <p><?= count(array_filter($projects, fn($p) => $p['status'] === 'approved')) ?></p>
        </div>
        <div class="card pending-projects">
            <h3>Pending</h3>
            <p><?= count(array_filter($projects, fn($p) => $p['status'] === 'pending')) ?></p>
        </div>
    </div>

    <!-- ===================== -->
    <!-- Projects Table -->
    <!-- ===================== -->
    <div class="projects-table-container">
        <h2>Project Submissions</h2>
        <?php if(empty($projects)): ?>
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
                        <tr data-project-id="<?= $proj['project_id'] ?>">
                            <td><?= htmlspecialchars($proj['title']) ?></td>
                            <td><?= htmlspecialchars($proj['student_name']) ?></td>
                            <td><?= htmlspecialchars($proj['sdg_name']) ?></td>
                            <td class="status <?= $proj['status'] ?>"><?= ucfirst($proj['status']) ?></td>
                            <td><?= date("M d, Y H:i", strtotime($proj['date_submitted'])) ?></td>
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

    <!-- ===================== -->
    <!-- Admin Management Panel -->
    <!-- ===================== -->
    <div class="admin-management">
        <h2>Manage Admins</h2>

        <div class="existing-admins">
            <h3>Current Admins</h3>
            <?php if(empty($admins)): ?>
                <p>No admins found.</p>
            <?php else: ?>
                <ul>
                    <?php foreach($admins as $admin): ?>
                        <li>
                            <?= htmlspecialchars($admin['username']) ?> (<?= htmlspecialchars($admin['email']) ?>)
                            - Created: <?= date("M d, Y", strtotime($admin['date_created'])) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="add-admin">
            <h3>Add New Admin</h3>
            <form id="addAdminForm" method="POST" autocomplete="off">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" id="addAdminBtn">Add Admin</button>
            </form>
            <div id="addAdminFeedback" class="feedback"></div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<!-- ===================== -->
<!-- Admin Dashboard JS -->
<!-- ===================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    // Feedback element
    const feedback = document.createElement("div");
    feedback.className = "feedback";
    document.body.prepend(feedback);

    const showFeedback = (message, type = "error") => {
        feedback.textContent = message;
        feedback.className = `feedback ${type}`;
        feedback.style.opacity = 1;
        feedback.style.transform = "translateY(0)";
        setTimeout(() => {
            feedback.style.opacity = 0;
            feedback.style.transform = "translateY(-10px)";
        }, 4000);
    };

    // Update summary cards
    const updateSummaryCards = () => {
        const cards = {
            total: document.querySelector(".total-projects p"),
            approved: document.querySelector(".approved-projects p"),
            pending: document.querySelector(".pending-projects p"),
        };

        const rows = Array.from(document.querySelectorAll(".projects-table tbody tr"));
        const counts = { approved: 0, pending: 0 };

        rows.forEach(row => {
            const status = row.querySelector(".status").textContent.toLowerCase();
            if (counts[status] !== undefined) counts[status]++;
        });

        cards.total.textContent = rows.length;
        cards.approved.textContent = counts.approved;
        cards.pending.textContent = counts.pending;
    };

    // Handle approve/reject
    const handleAction = async (button) => {
        const row = button.closest("tr");
        const projectId = row.dataset.projectId;
        const status = button.dataset.status;

        button.disabled = true;

        try {
            const response = await fetch("../api/admin/update_project_status.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ project_id: projectId, status })
            });

            const result = await response.json();

            if (result.status === "success") {
                // Remove row if rejected
                if (status === "rejected") {
                    row.remove();
                } else {
                    const statusCell = row.querySelector(".status");
                    statusCell.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusCell.className = "status " + status;
                    row.querySelector(".actions").innerHTML = "<span>-</span>";
                }

                showFeedback(result.message, "success");
                updateSummaryCards();
            } else {
                showFeedback(result.message || "Action failed", "error");
            }
        } catch (error) {
            showFeedback("Server error: " + error.message, "error");
        } finally {
            button.disabled = false;
        }
    };

    // Attach buttons, prevent double listeners
    const attachButtons = () => {
        document.querySelectorAll(".approve-btn, .reject-btn").forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener("click", () => handleAction(newBtn));
        });
    };

    attachButtons();
    updateSummaryCards();

    // Add admin redirect
    const addAdminBtn = document.getElementById("addAdminBtn");
    if (addAdminBtn) {
        addAdminBtn.addEventListener("click", () => {
            window.location.href = "admin_register.php";
        });
    }
});
</script>
