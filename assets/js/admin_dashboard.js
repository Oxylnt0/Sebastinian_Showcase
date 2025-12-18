/* ============================================== 
   Sebastinian Showcase Admin Panel - Ultimate JS
   ============================================== */

document.addEventListener("DOMContentLoaded", () => {

    /* =========================
        Feedback Toast System
    ========================= */
    const feedback = document.createElement("div");
    feedback.className = "feedback";
    document.body.prepend(feedback);

    const showFeedback = (msg, type = "error") => {
        feedback.textContent = msg;
        feedback.className = `feedback ${type}`;
        feedback.style.opacity = 1;
        feedback.style.transform = "translateY(0)";
        setTimeout(() => {
            feedback.style.opacity = 0;
            feedback.style.transform = "translateY(-20px)";
        }, 4000);
    };

    /* =========================
        Tabs Switching
    ========================= */
    const tabs = document.querySelectorAll(".tab-btn");
    const panes = document.querySelectorAll(".tab-pane");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(t => t.classList.remove("active"));
            panes.forEach(p => p.classList.remove("active"));

            tab.classList.add("active");
            const activePane = document.getElementById(tab.dataset.tab);
            activePane.classList.add("active");

            if (tab.dataset.tab === "manage-admins") loadAdmins();
            if (tab.dataset.tab === "projects") loadProjects();
        });
    });

    /* ============================================== 
    Admin Management & Authorization
   ============================================== */

    // 1. Function to load the data from the database
    const loadAdmins = async () => {
        const container = document.getElementById("admins-container");
        if (!container) return;
        container.innerHTML = `<p>Fetching researchers...</p>`;

        try {
            const res = await fetch("/Sebastinian_Showcase/api/admin/get_admins.php");
            const data = await res.json();
            if (data.status !== "success") throw new Error(data.message);

            let html = `<table class="projects-table">
                <thead><tr><th>Username</th><th>Email</th><th>Full Name</th><th>Actions</th></tr></thead>
                <tbody>`;
            
            data.data.forEach(admin => {
                html += `
                    <tr data-user-id="${admin.user_id}">
                        <td>${admin.username}</td>
                        <td>${admin.email}</td>
                        <td>${admin.full_name}</td>
                        <td><button class="delete-admin-btn">Revoke Access</button></td>
                    </tr>`;
            });
            html += `</tbody></table>`;
            container.innerHTML = html;
            // No need to call attachAdminEvents() here anymore!
        } catch (err) {
            showFeedback(err.message, "error");
        }
    };

    // 2. Handle clicks on "Revoke Access" buttons using Delegation
    const adminContainer = document.getElementById("admins-container");
    if (adminContainer) {
        adminContainer.addEventListener("click", async (e) => {
            // Check if the clicked element is the Revoke button
            const btn = e.target.closest(".delete-admin-btn");
            if (!btn) return;

            if (!confirm("Revoke this admin's access?")) return;
            
            const row = btn.closest("tr");
            const userId = row.dataset.userId;

            try {
                const res = await fetch("/Sebastinian_Showcase/api/admin/delete_admin.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ user_id: userId })
                });
                const data = await res.json();
                if (data.status === "success") {
                    row.remove();
                    showFeedback("Access revoked.", "success");
                }
            } catch (err) { showFeedback("Delete failed.", "error"); }
        });
    }

    // 3. Handle the Add Admin Form directly
    const addAdminForm = document.getElementById("addAdminForm");
    if (addAdminForm) {
        addAdminForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = Object.fromEntries(new FormData(addAdminForm).entries());

            // Validation (Email, Password Strength, etc.)
            if (!formData.email.toLowerCase().endsWith("@sscr.edu")) {
                showFeedback("Only @sscr.edu emails are allowed.", "error");
                return;
            }

            try {
                const res = await fetch("/Sebastinian_Showcase/api/admin/add_admin.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(formData)
                });
                const data = await res.json();
                if (data.status !== "success") throw new Error(data.message);

                addAdminForm.reset();
                loadAdmins(); // Reload table to show new admin
                showFeedback("Admin authorized successfully", "success");
            } catch (err) {
                showFeedback(err.message, "error");
            }
        });
    }

    /* ==========================================
        DYNAMIC PROJECT ACTIONS (Approve/Delete)
        This is the fix for your buttons
    ========================================== */
    const projectsContainer = document.getElementById("projects-container");

    if (projectsContainer) {
        projectsContainer.addEventListener("click", async (e) => {
            const btn = e.target.closest("button");
            if (!btn) return;

            const row = btn.closest("tr");
            const projectId = row.dataset.projectId;

            // --- APPROVE LOGIC ---
            if (btn.classList.contains("approve-btn")) {
                try {
                    // Find the ID from the row's data attribute
                    const row = btn.closest("tr");
                    const projectId = row.dataset.projectId; // Matches <tr data-project-id="...">

                    const res = await fetch("../../api/admin/update_project_status.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ 
                            project_id: projectId, // PHP expects 'project_id'
                            status: 'approved'      // PHP expects 'approved' or 'rejected'
                        })
                    });

                    const data = await res.json();
                    
                    if (data.status === "success" || data.status === "success_response") {
                        showFeedback("Research Approved!", "success");
                        loadProjects(); 
                    } else {
                        // This will show your "Invalid project ID or status" error if it fails
                        showFeedback(data.message, "error"); 
                    }
                } catch (err) { 
                    console.error(err);
                    showFeedback("Connection error.", "error"); 
                }
            }

            // --- REJECT LOGIC ---
            if (btn.classList.contains("reject-btn")) {
                if (confirm("Are you sure you want to reject this research?")) {
                    await handleStatusUpdate(projectId, 'rejected');
                }
            }

            // --- DELETE LOGIC ---
            if (btn.classList.contains("delete-project-btn")) {
                if (!confirm("Are you sure? This research will be permanently deleted.")) return;
                try {
                    const res = await fetch("/Sebastinian_Showcase/api/admin/delete_project.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ project_id: projectId })
                    });
                    const data = await res.json();
                    if (data.status === "success") {
                        row.remove();
                        showFeedback("Research deleted.", "success");
                    } else {
                        throw new Error(data.message);
                    }
                } catch (err) { showFeedback(err.message, "error"); }
            }
        });
    }

    // Helper function to keep code clean
    async function handleStatusUpdate(projectId, newStatus) {
        try {
            const res = await fetch("../../api/admin/update_project_status.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ 
                    project_id: projectId, 
                    status: newStatus 
                })
            });
            const data = await res.json();
            if (data.status === "success" || data.status === "success_response") {
                // Using a template literal for the message
                const msg = newStatus === 'approved' ? "Research Approved!" : "Research Rejected.";
                alert(msg); 
                location.reload(); // Refresh to update the counts and lists
            } else {
                alert(data.message);
            }
        } catch (err) { console.error(err); }
    }

    /* =========================
        Research/Thesis Loading
    ========================= */
    const loadProjects = async (search = "") => {
        const container = document.getElementById("projects-container");
        if (!container) return;
        
        try {
            let url = "/Sebastinian_Showcase/api/admin/search_projects.php";
            if (search) url += `?query=${encodeURIComponent(search)}`;
            const res = await fetch(url);
            const data = await res.json();

            let html = `<table class="projects-table">
                <thead><tr><th>Thesis Title</th><th>Student</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>`;
                data.data.projects.forEach(proj => {
                    html += `
                        <tr data-project-id="${proj.project_id}">
                            <td>${proj.title}</td>
                            <td>${proj.student_name}</td>
                            <td><span class="status ${proj.status}">${proj.status}</span></td>
                            <td style="display:flex; gap:10px;">
                                ${proj.status === "pending" ? `
                                    <button class="approve-btn">Approve</button>
                                    <button class="reject-btn">Reject</button>
                                ` : ""}
                                <button class="delete-project-btn">Delete</button>
                            </td>
                        </tr>`;
                });
            container.innerHTML = html + `</tbody></table>`;
        } catch (err) { showFeedback("Failed to load archive.", "error"); }
    };

    // Initial Load
    loadAdmins();
    loadProjects();
});