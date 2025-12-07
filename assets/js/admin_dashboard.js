/* ============================================== 
   Sebastinian Showcase Admin Panel - Ultimate JS
   Premium Red & Gold Theme
   Clean, Professional, Fully Optimized
   ============================================== */

document.addEventListener("DOMContentLoaded", () => {

    /* =========================
       Feedback Toast
    ========================= */
    const feedback = document.createElement("div");
    feedback.className = "feedback";
    document.body.prepend(feedback);

    const showFeedback = (msg, type = "error") => {
        feedback.textContent = msg;
        feedback.className = `feedback ${type}`;
        feedback.style.opacity = 1;
        feedback.style.transform = "translateY(0)";
        feedback.style.pointerEvents = "auto";
        setTimeout(() => {
            feedback.style.opacity = 0;
            feedback.style.transform = "translateY(-20px)";
            feedback.style.pointerEvents = "none";
        }, 4000);
    };

    /* =========================
       Tabs Switching with Smooth Fade
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
            activePane.scrollIntoView({ behavior: "smooth", block: "start" });

            // Load content dynamically for AJAX tabs
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
        container.innerHTML = `<p>Loading admins...</p>`;

        try {
            const res = await fetch("/Sebastinian_Showcase/api/admin/get_admins.php");
            const data = await res.json();
            if (data.status !== "success") throw new Error(data.message);

            let html = `
                <table class="projects-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>`;
            data.data.forEach(admin => {
                html += `
                    <tr data-user-id="${admin.user_id}">
                        <td>${admin.username}</td>
                        <td>${admin.email}</td>
                        <td>${admin.full_name}</td>
                        <td>${new Date(admin.date_created).toLocaleDateString()}</td>
                        <td><button class="delete-admin-btn">Delete</button></td>
                    </tr>`;
            });
            html += `</tbody></table>`;
            container.innerHTML = html;

            attachAdminEvents();
        } catch (err) {
            showFeedback(err.message, "error");
        }
    };

    const attachAdminEvents = () => {
        // Delete admin
        document.querySelectorAll(".delete-admin-btn").forEach(btn => {
            btn.addEventListener("click", async () => {
                const row = btn.closest("tr");
                const userId = row.dataset.userId;
                if (!confirm("Are you sure you want to delete this admin?")) return;

                try {
                    const res = await fetch("/Sebastinian_Showcase/api/admin/delete_admin.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ user_id: userId })
                    });
                    const data = await res.json();
                    if (data.status !== "success") throw new Error(data.message);

                    row.remove();
                    showFeedback("Admin deleted successfully", "success");
                } catch (err) {
                    showFeedback(err.message, "error");
                }
            });
        });

        // Add admin using form
        const addForm = document.getElementById("addAdminForm");
        if (addForm) {
            addForm.addEventListener("submit", async e => {
                e.preventDefault();
                const formData = Object.fromEntries(new FormData(addForm).entries());

                if (formData.password !== formData.confirm_password) {
                    showFeedback("Passwords do not match", "error");
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

                    addForm.reset();
                    loadAdmins();
                    showFeedback("Admin added successfully", "success");
                } catch (err) {
                    showFeedback(err.message, "error");
                }
            });
        }
    };

    /* =========================
       Project Management
    ========================= */
    let projectSearchTimeout = null;
    const loadProjects = async (search = "") => {
        const container = document.getElementById("projects-container");
        if (!container) return;
        container.innerHTML = `<p>Loading projects...</p>`;

        try {
            let url = "/Sebastinian_Showcase/api/admin/search_projects.php";
            if (search) url += `?query=${encodeURIComponent(search)}`;

            const res = await fetch(url);
            const data = await res.json();
            if (data.status !== "success") throw new Error(data.message);

            let html = `<table class="projects-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>`;
            data.data.projects.forEach(proj => {
                html += `
                    <tr data-project-id="${proj.project_id}" data-user-id="${proj.user_id}">
                        <td>${proj.title}</td>
                        <td><span class="view-student">${proj.student_name}</span></td>
                        <td class="status-cell">
                            <span class="status ${proj.status}">
                                ${proj.status.charAt(0).toUpperCase() + proj.status.slice(1)}
                            </span>
                        </td>
                        <td>${new Date(proj.date_submitted).toLocaleString()}</td>
                        <td>
                            ${proj.status === "pending" ? 
                                `<button class="approve-btn" data-status="approved">Approve</button>
                                 <button class="reject-btn" data-status="rejected">Reject</button>` : ""}
                            <button class="delete-project-btn">Delete</button>
                        </td>
                    </tr>`;
            });
            html += `</tbody></table>`;
            container.innerHTML = html;

            attachProjectEvents();
        } catch (err) {
            container.innerHTML = `<p>Error loading projects.</p>`;
            showFeedback(err.message, "error");
        }
    };

    const attachProjectEvents = () => {
        // Approve/Reject
        document.querySelectorAll(".approve-btn, .reject-btn").forEach(btn => {
            btn.addEventListener("click", async () => {
                const row = btn.closest("tr");
                const projectId = row.dataset.projectId;
                const status = btn.dataset.status;

                try {
                    const res = await fetch("/Sebastinian_Showcase/api/admin/update_project_status.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ project_id: projectId, status })
                    });
                    const data = await res.json();
                    if (data.status !== "success") throw new Error(data.message);

                    const cell = row.querySelector(".status");
                    if (status === "rejected") {
                        row.remove();
                    } else {
                        cell.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        cell.className = "status " + status;
                        row.querySelector("td:last-child").innerHTML = `<button class="delete-project-btn">Delete</button>`;
                        attachProjectEvents(); // rebind newly added delete buttons
                    }

                    showFeedback(data.message, "success");
                } catch (err) {
                    showFeedback(err.message, "error");
                }
            });
        });

        // Delete Project
        document.querySelectorAll(".delete-project-btn").forEach(btn => {
            btn.addEventListener("click", async () => {
                const row = btn.closest("tr");
                const projectId = row.dataset.projectId;
                if (!confirm("Are you sure you want to delete this project?")) return;

                try {
                    const res = await fetch("/Sebastinian_Showcase/api/admin/delete_project.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ project_id: projectId })
                    });
                    const data = await res.json();
                    if (data.status !== "success") throw new Error(data.message);

                    row.remove();
                    showFeedback("Project deleted", "success");
                } catch (err) {
                    showFeedback(err.message, "error");
                }
            });
        });

        // Student Modal
        document.querySelectorAll(".view-student").forEach(span => {
            span.addEventListener("click", async () => {
                const row = span.closest("tr");
                const userId = row.dataset.userId;

                try {
                    const res = await fetch(`/Sebastinian_Showcase/api/admin/get_student_details.php?user_id=${userId}`);
                    const data = await res.json();
                    if (data.status !== "success") throw new Error(data.message);

                    const student = data.data.student;
                    const projects = data.data.projects;
                    const container = document.getElementById("studentProfileContainer");
                    container.innerHTML = `
                        <h3>${student.full_name} (${student.email})</h3>
                        <p>Registered: ${new Date(student.date_created).toLocaleDateString()}</p>
                        <h4>Projects</h4>
                        <ul>
                            ${projects.map(p => `<li>${p.title} - ${p.status}</li>`).join("")}
                        </ul>
                    `;
                    const modal = document.getElementById("studentModal");
                    modal.style.display = "flex";
                    modal.querySelector(".modal-content").scrollIntoView({ behavior: "smooth" });

                } catch (err) {
                    showFeedback(err.message, "error");
                }
            });
        });

        // Search input with debouncing
        const searchInput = document.getElementById("projectsSearch");
        if (searchInput) {
            searchInput.addEventListener("input", () => {
                clearTimeout(projectSearchTimeout);
                projectSearchTimeout = setTimeout(() => loadProjects(searchInput.value.trim()), 400);
            });
        }
    };

    /* =========================
       Student Modal Close
    ========================= */
    const modal = document.getElementById("studentModal");
    if (modal) {
        modal.querySelector(".close").addEventListener("click", () => modal.style.display = "none");
        window.addEventListener("click", e => { if (e.target === modal) modal.style.display = "none"; });
    }

    /* =========================
       Initialize Dashboard
    ========================= */
    loadAdmins();
    loadProjects();
});
