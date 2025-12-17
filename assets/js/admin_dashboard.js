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
       Password Visibility Toggle
    ========================= */
    document.querySelectorAll(".toggle-password").forEach(icon => {
        icon.addEventListener("click", function() {
            const targetId = this.getAttribute("data-target");
            const input = document.getElementById(targetId);
            
            const isPassword = input.getAttribute("type") === "password";
            input.setAttribute("type", isPassword ? "text" : "password");

            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
            
            // Visual feedback on the input
            if (isPassword) {
                input.style.borderColor = "#D4AF37";
            } else {
                input.style.borderColor = "#ddd";
            }
        });
    });

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
       Admin Management & Authorization
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

    const attachAdminEvents = () => {
        const addForm = document.getElementById("addAdminForm");
        if (addForm) {
            addForm.onsubmit = async (e) => {
                e.preventDefault();
                const formData = Object.fromEntries(new FormData(addForm).entries());
                
                // Client-side Security Enforcement
                if (!formData.email.toLowerCase().endsWith("@sscr.edu")) {
                    showFeedback("Only @sscr.edu emails are allowed.", "error");
                    return;
                }

                const pass = formData.password;
                const strongPass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/.test(pass);
                if (!strongPass) {
                    showFeedback("Password must be 12+ chars with Uppercase, Number, and Special char.", "error");
                    return;
                }

                if (pass !== formData.confirm_password) {
                    showFeedback("Passwords do not match.", "error");
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
                    showFeedback("Admin authorized successfully", "success");
                } catch (err) {
                    showFeedback(err.message, "error");
                }
            };
        }

        document.querySelectorAll(".delete-admin-btn").forEach(btn => {
            btn.onclick = async () => {
                if (!confirm("Revoke this admin's access?")) return;
                const userId = btn.closest("tr").dataset.userId;
                try {
                    const res = await fetch("/Sebastinian_Showcase/api/admin/delete_admin.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ user_id: userId })
                    });
                    const data = await res.json();
                    if (data.status === "success") {
                        btn.closest("tr").remove();
                        showFeedback("Access revoked.", "success");
                    }
                } catch (err) { showFeedback("Delete failed.", "error"); }
            };
        });
    };

    /* =========================
       Research/Thesis Management
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
                        <td>
                            ${proj.status === "pending" ? `<button class="approve-btn">Approve</button>` : ""}
                            <button class="delete-project-btn">Delete</button>
                        </td>
                    </tr>`;
            });
            container.innerHTML = html + `</tbody></table>`;
            // Attach approval/delete events here...
        } catch (err) { showFeedback("Failed to load archive.", "error"); }
    };

    // Initial Load
    loadAdmins();
    loadProjects();
});