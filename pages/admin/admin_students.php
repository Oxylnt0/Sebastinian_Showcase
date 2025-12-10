<?php
// ===============================================
// pages/admin/admin_students.php - Sebastinian Showcase Admin Panel
// ===============================================

session_start();
require_once("../api/utils/auth_check.php");
auth_check(['admin']);
?>

<?php include("header.php"); ?>

<div class="admin-students-container">
    <h1>Students Management</h1>

    <!-- ===================== -->
    <!-- Search Bar -->
    <!-- ===================== -->
    <div class="search-bar">
        <input type="text" id="searchStudentsInput" placeholder="Search by student name or email">
        <button id="searchStudentsBtn">Search</button>
    </div>

    <!-- ===================== -->
    <!-- Students Table -->
    <!-- ===================== -->
    <table class="students-table">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Registration Date</th>
                <th>Total Projects</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="studentsTableBody">
            <!-- AJAX-loaded rows go here -->
        </tbody>
    </table>

    <!-- ===================== -->
    <!-- Student Details Modal -->
    <!-- ===================== -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalStudentName">Student Details</h2>
            <p id="modalStudentEmail"></p>
            <p id="modalStudentRegDate"></p>

            <h3>Projects</h3>
            <table id="modalProjectsTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- AJAX-loaded student projects -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================== -->
    <!-- Feedback -->
    <!-- ===================== -->
    <div id="studentsFeedback" class="feedback"></div>
</div>

<?php include("footer.php"); ?>

<script>
// ===============================================
// admin_students.js – Dynamic Students Table + Modal
// ===============================================
document.addEventListener("DOMContentLoaded", () => {

    const tableBody = document.getElementById("studentsTableBody");
    const feedback = document.getElementById("studentsFeedback");
    const searchInput = document.getElementById("searchStudentsInput");
    const searchBtn = document.getElementById("searchStudentsBtn");

    const modal = document.getElementById("studentModal");
    const modalClose = modal.querySelector(".close");
    const modalName = document.getElementById("modalStudentName");
    const modalEmail = document.getElementById("modalStudentEmail");
    const modalRegDate = document.getElementById("modalStudentRegDate");
    const modalProjectsTableBody = modal.querySelector("tbody");

    const showFeedback = (msg, type="error") => {
        feedback.textContent = msg;
        feedback.className = `feedback ${type}`;
        feedback.style.opacity = 1;
        setTimeout(() => { feedback.style.opacity = 0; }, 4000);
    };

    // -----------------------------
    // Fetch students
    // -----------------------------
    const loadStudents = async (query="") => {
        try {
            let url = "../api/admin/get_students.php";
            if (query) url += "?search=" + encodeURIComponent(query);

            const response = await fetch(url);
            const result = await response.json();

            if (result.status === "success") {
                renderStudents(result.data);
            } else {
                showFeedback(result.message || "Failed to load students");
            }
        } catch (err) {
            showFeedback("Server error: " + err.message);
        }
    };

    // -----------------------------
    // Render students table
    // -----------------------------
    const renderStudents = (students) => {
        tableBody.innerHTML = "";

        if (!students.length) {
            tableBody.innerHTML = `<tr><td colspan="5">No students found.</td></tr>`;
            return;
        }

        students.forEach(s => {
            const tr = document.createElement("tr");
            tr.dataset.userId = s.user_id;

            tr.innerHTML = `
                <td><span class="student-name" data-user-id="${s.user_id}">${s.full_name}</span></td>
                <td>${s.email}</td>
                <td>${new Date(s.date_created).toLocaleDateString()}</td>
                <td>${s.total_projects}</td>
                <td>
                    <button class="view-btn">View</button>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        attachViewButtons();
    };

    // -----------------------------
    // Attach "View" buttons for modal
    // -----------------------------
    const attachViewButtons = () => {
        document.querySelectorAll(".view-btn").forEach(btn => {
            btn.onclick = () => {
                const userId = btn.closest("tr").dataset.userId;
                openStudentModal(userId);
            });
        });
    };

    // -----------------------------
    // Open student modal
    // -----------------------------
    const openStudentModal = async (userId) => {
        try {
            const response = await fetch("../api/admin/get_student_details.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ user_id: userId })
            });

            const result = await response.json();

            if (result.status === "success") {
                const student = result.data.student;
                const projects = result.data.projects;

                modalName.textContent = student.full_name;
                modalEmail.textContent = "Email: " + student.email;
                modalRegDate.textContent = "Registered: " + new Date(student.date_created).toLocaleDateString();

                // Render projects
                modalProjectsTableBody.innerHTML = "";
                if (!projects.length) {
                    modalProjectsTableBody.innerHTML = `<tr><td colspan="4">No projects found.</td></tr>`;
                } else {
                    projects.forEach(p => {
                        const tr = document.createElement("tr");
                        tr.dataset.projectId = p.project_id;
                        tr.innerHTML = `
                            <td>${p.title}</td>
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
                        modalProjectsTableBody.appendChild(tr);
                    });

                    attachModalActionButtons();
                }

                modal.style.display = "block";
            } else {
                showFeedback(result.message || "Failed to load student details");
            }
        } catch (err) {
            showFeedback("Server error: " + err.message);
        }
    };

    // -----------------------------
    // Modal buttons for projects
    // -----------------------------
    const attachModalActionButtons = () => {
        modal.querySelectorAll(".approve-btn").forEach(btn => {
            btn.onclick = () => handleModalProjectAction(btn.closest("tr").dataset.projectId, "approved");
        });
        modal.querySelectorAll(".reject-btn").forEach(btn => {
            btn.onclick = () => handleModalProjectAction(btn.closest("tr").dataset.projectId, "rejected");
        });
        modal.querySelectorAll(".delete-btn").forEach(btn => {
            btn.onclick = () => {
                if (confirm("Are you sure you want to delete this project?")) {
                    handleModalProjectAction(btn.closest("tr").dataset.projectId, "delete");
                }
            };
        });
    };

    const handleModalProjectAction = async (projectId, action) => {
        let url = action === "delete" ? "../api/admin/delete_project.php" : "../api/admin/update_project_status.php";
        const body = { project_id: projectId };
        if (action !== "delete") body.status = action;

        try {
            const response = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(body)
            });

            const result = await response.json();
            if (result.status === "success") {
                showFeedback(result.message, "success");
                openStudentModal(projectId); // reload modal
                loadStudents(searchInput.value); // refresh students table
            } else {
                showFeedback(result.message || "Action failed");
            }
        } catch (err) {
            showFeedback("Server error: " + err.message);
        }
    };

    // -----------------------------
    // Close modal
    // -----------------------------
    modalClose.onclick = () => modal.style.display = "none";
    window.onclick = (event) => {
        if (event.target === modal) modal.style.display = "none";
    };

    // -----------------------------
    // Search students
    // -----------------------------
    searchBtn.addEventListener("click", () => loadStudents(searchInput.value));
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") loadStudents(searchInput.value);
    });

    // Initial load
    loadStudents();
});
</script>
