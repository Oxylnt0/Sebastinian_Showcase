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

    /* =========================
        Admin Management
    ========================= */
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
            attachAdminEvents();
        } catch (err) {
            showFeedback(err.message, "error");
        }
    };

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
                    const res = await fetch("/Sebastinian_Showcase/api/admin/approve_project.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ project_id: projectId })
                    });
                    const data = await res.json();
                    if (data.status === "success") {
                        showFeedback("Research Approved!", "success");
                        loadProjects(); // Refresh the list
                    } else {
                        throw new Error(data.message);
                    }
                } catch (err) { showFeedback(err.message, "error"); }
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
                            ${proj.status === "pending" ? `<button class="approve-btn">Approve</button>` : ""}
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