<?php
// ===============================================
// pages/admin/admin_projects.php- Sebastinian Showcase Admin Panel
// ===============================================

session_start();
require_once("../api/utils/auth_check.php");
auth_check(['admin']);
?>

<?php include("header.php"); ?>

<div class="admin-projects-container">
    <h1>Projects Management</h1>

    <!-- ===================== -->
    <!-- Search Bar -->
    <!-- ===================== -->
    <div class="search-bar">
        <input type="text" id="searchProjectsInput" placeholder="Search by project title or student name">
        <button id="searchProjectsBtn">Search</button>
    </div>

    <!-- ===================== -->
    <!-- Projects Table -->
    <!-- ===================== -->
    <table class="projects-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Student</th>
                <th>Status</th>
                <th>Date Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="projectsTableBody">
            <!-- AJAX-loaded rows go here -->
        </tbody>
    </table>

    <!-- ===================== -->
    <!-- Loading / Feedback -->
    <!-- ===================== -->
    <div id="projectsFeedback" class="feedback"></div>
</div>

<?php include("footer.php"); ?>

<script>
// ===============================================
// admin_projects.js – Dynamic Projects Table
// ===============================================
document.addEventListener("DOMContentLoaded", () => {

    const tableBody = document.getElementById("projectsTableBody");
    const feedback = document.getElementById("projectsFeedback");
    const searchInput = document.getElementById("searchProjectsInput");
    const searchBtn = document.getElementById("searchProjectsBtn");

    const showFeedback = (msg, type="error") => {
        feedback.textContent = msg;
        feedback.className = `feedback ${type}`;
        feedback.style.opacity = 1;
        setTimeout(() => { feedback.style.opacity = 0; }, 4000);
    };

    // -----------------------------
    // Fetch projects
    // -----------------------------
    const loadProjects = async (query="") => {
        try {
            let url = "../api/admin/get_projects.php";
            if (query) url += "?search=" + encodeURIComponent(query);

            const response = await fetch(url);
            const result = await response.json();

            if (result.status === "success") {
                renderProjects(result.data);
            } else {
                showFeedback(result.message || "Failed to load projects");
            }
        } catch (err) {
            showFeedback("Server error: " + err.message);
        }
    };

    // -----------------------------
    // Render projects table
    // -----------------------------
    const renderProjects = (projects) => {
        tableBody.innerHTML = "";

        if (!projects.length) {
            tableBody.innerHTML = `<tr><td colspan="5">No projects found.</td></tr>`;
            return;
        }

        projects.forEach(p => {
            const tr = document.createElement("tr");
            tr.dataset.projectId = p.project_id;

            tr.innerHTML = `
                <td>${p.title}</td>
                <td><span class="student-name" data-user-id="${p.user_id}">${p.student_name}</span></td>
                <td class="status ${p.status}">${p.status.charAt(0).toUpperCase() + p.status.slice(1)}</td>
                <td>${new Date(p.date_submitted).toLocaleString()}</td>
                <td class="actions">
                    ${p.status === 'pending' ? 
                        `<button class="approve-btn" data-status="approved">Approve</button>
                         <button class="reject-btn" data-status="rejected">Reject</button>` 
                        : `<span>-</span>`}
                    <button class="delete-btn">Delete</button>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        attachActionButtons();
    };

    // -----------------------------
    // Approve / Reject / Delete
    // -----------------------------
    const handleAction = async (projectId, action) => {
        let url = "";
        let body = { project_id: projectId };

        if (action === "delete") {
            url = "../api/admin/delete_project.php";
        } else {
            url = "../api/admin/update_project_status.php";
            body.status = action;
        }

        try {
            const response = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(body)
            });

            const result = await response.json();
            if (result.status === "success") {
                showFeedback(result.message, "success");
                loadProjects(searchInput.value);
            } else {
                showFeedback(result.message || "Action failed");
            }
        } catch (err) {
            showFeedback("Server error: " + err.message);
        }
    };

    // -----------------------------
    // Attach buttons
    // -----------------------------
    const attachActionButtons = () => {
        document.querySelectorAll(".approve-btn").forEach(btn => {
            btn.onclick = () => handleAction(btn.closest("tr").dataset.projectId, "approved");
        });
        document.querySelectorAll(".reject-btn").forEach(btn => {
            btn.onclick = () => handleAction(btn.closest("tr").dataset.projectId, "rejected");
        });
        document.querySelectorAll(".delete-btn").forEach(btn => {
            btn.onclick = () => {
                if (confirm("Are you sure you want to delete this project?")) {
                    handleAction(btn.closest("tr").dataset.projectId, "delete");
                }
            };
        });
    };

    // -----------------------------
    // Search button
    // -----------------------------
    searchBtn.addEventListener("click", () => {
        loadProjects(searchInput.value);
    });

    // Optional: Enter key triggers search
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") loadProjects(searchInput.value);
    });

    // Initial load
    loadProjects();
});
</script>
