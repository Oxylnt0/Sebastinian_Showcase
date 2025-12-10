document.addEventListener("DOMContentLoaded", () => {

    // =========================================================
    // 0. SINGLETON LOCK & GLOBAL CONFIG
    // =========================================================
    if (window.adminDashboardLoaded) return; 
    window.adminDashboardLoaded = true;

    // Elements
    const API_BASE = '../../api/admin/';
    const feedbackToast = document.getElementById('dashboardFeedback');
    const csrfToken = document.getElementById('csrfToken')?.value || 'missing'; // Assuming token is available somewhere
    
    // Tab Elements
    const tabs = document.querySelectorAll(".tab-btn");
    const panes = document.querySelectorAll(".tab-pane");

    // Containers for dynamic content
    const adminsContainer = document.getElementById("admins-container");
    const projectsContainer = document.getElementById("projects-container");
    const addAdminForm = document.getElementById("addAdminForm");

    let projectSearchTimeout = null;
    
    // --- Utility Functions ---
    const showFeedback = (msg, type = "error") => {
        if (!feedbackToast) return;
        feedbackToast.textContent = msg;
        feedbackToast.className = `feedback ${type}`;
        feedbackToast.style.opacity = 1;
        feedbackToast.style.transform = "translateY(0)";
        feedbackToast.style.pointerEvents = "auto";
        setTimeout(() => {
            feedbackToast.style.opacity = 0;
            feedbackToast.style.transform = "translateY(-20px)";
            feedbackToast.style.pointerEvents = "none";
        }, 4000);
    };

    const startLoading = (container) => {
        container.innerHTML = `<div class="loading-state-overlay"><div class="sebastinian-loader"></div><p>Loading data...</p></div>`;
        container.classList.add('loading');
    };

    const stopLoading = (container) => {
        container.classList.remove('loading');
    };

    // =========================================================
    // 1. CINEMATIC NUMBERS & TILT (Dashboard Tab)
    // =========================================================
    
    const animateCounters = () => {
        const counters = document.querySelectorAll('.stat-card .counter');
        const speed = 1500;

        counters.forEach(counter => {
            const target = +counter.innerText;
            const start = 0;
            const increment = target / (speed / 16);

            let current = start;
            const updateCount = () => {
                current += increment;
                if (current < target) {
                    counter.innerText = Math.ceil(current);
                    requestAnimationFrame(updateCount);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });
    };
    
    // Observer for stats animation on scroll visibility
    const statsGrid = document.querySelector('.stats-overview-grid');
    if (statsGrid) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counterObserver.observe(statsGrid);
    }
    
    // Init 3D Tilt
    if (typeof VanillaTilt !== "undefined") {
        VanillaTilt.init(document.querySelectorAll("[data-tilt]"), {
            max: 5, speed: 1000, scale: 1.01, glare: true, "max-glare": 0.1,
        });
    }

    // =========================================================
    // 2. TABS SWITCHING & SPA MANAGEMENT
    // =========================================================

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(t => t.classList.remove("active"));
            panes.forEach(p => p.classList.remove("active"));

            tab.classList.add("active");
            const activePane = document.getElementById(tab.dataset.tab);
            activePane.classList.add("active");
            
            const tabName = tab.dataset.tab;
            if (tabName === "manage-admins") loadAdmins();
            if (tabName === "projects") loadProjects();
        });
    });
    
    // =========================================================
    // 3. ADMIN MANAGEMENT TAB LOGIC
    // =========================================================
    
    // --- 3.1 Fetch & Render Admins ---
    const loadAdmins = async () => {
        if (!adminsContainer) return;
        startLoading(adminsContainer);

        try {
            const res = await fetch(`${API_BASE}get_admins.php`);
            const data = await res.json();
            stopLoading(adminsContainer);
            if (data.status !== "success") throw new Error(data.message);

            let html = `
                <table class="responsive-table admin-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminsTableBody">`; 
            data.data.forEach(admin => {
                html += `
                    <tr data-user-id="${admin.user_id}">
                        <td><i class="fas fa-user-shield text-gold"></i> ${admin.username}</td>
                        <td>${admin.email}</td>
                        <td>${admin.full_name}</td>
                        <td>${new Date(admin.date_created).toLocaleDateString()}</td>
                        <td class="actions-cell">
                            <button class="action-btn delete-admin-btn" data-user-id="${admin.user_id}" title="Delete Admin"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
            });
            html += `</tbody></table>`;
            adminsContainer.innerHTML = html;

        } catch (err) {
            stopLoading(adminsContainer);
            adminsContainer.innerHTML = `<p class="error-message">Error fetching admins: ${err.message}</p>`;
        }
    };

    // --- 3.2 Delegate Admin Actions (Delete) ---
    if (adminsContainer) {
        adminsContainer.addEventListener("click", async (e) => {
            const btn = e.target.closest(".delete-admin-btn");
            if (!btn) return;

            const row = btn.closest("tr");
            const userId = btn.dataset.userId;

            if (!confirm(`CONFIRM: Permanently delete admin user ${row.querySelector('td').textContent.trim()}?`)) return;

            try {
                row.style.opacity = 0.5;
                const res = await fetch(`${API_BASE}delete_admin.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ user_id: userId, csrf_token: csrfToken })
                });
                const data = await res.json();
                if (data.status !== "success") throw new Error(data.message);

                row.style.transform = 'scale(0.9)';
                setTimeout(() => row.remove(), 300); 
                showFeedback("Admin deleted successfully", "success");
            } catch (err) {
                row.style.opacity = 1;
                showFeedback(err.message, "error");
            }
        });
    }
    
    // --- 3.3 Add Admin Form Submission ---
    if (addAdminForm) {
        addAdminForm.addEventListener("submit", async e => {
            e.preventDefault();
            const formData = Object.fromEntries(new FormData(addAdminForm).entries());

            if (formData.password !== formData.confirm_password) {
                showFeedback("Passwords do not match", "error");
                return;
            }

            try {
                const res = await fetch(`${API_BASE}add_admin.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ ...formData, csrf_token: csrfToken })
                });
                const data = await res.json();
                if (data.status !== "success") throw new Error(data.message);

                addAdminForm.reset();
                loadAdmins();
                showFeedback("Admin added successfully", "success");
            } catch (err) {
                showFeedback(err.message, "error");
            }
        });
    }

    // =========================================================
    // 4. PROJECT MANAGEMENT TAB LOGIC
    // =========================================================

    // --- 4.1 Fetch & Render Projects ---
    const loadProjects = async (search = "") => {
        if (!projectsContainer) return;
        startLoading(projectsContainer);

        try {
            let url = `${API_BASE}search_projects.php`;
            if (search) url += `?query=${encodeURIComponent(search)}`;

            const res = await fetch(url);
            const data = await res.json();
            stopLoading(projectsContainer);
            if (data.status !== "success") throw new Error(data.message);

            const projects = data.data.projects;
            
            if (!projects.length) {
                projectsContainer.innerHTML = `<p class="empty-state">No projects found.</p>`;
                return;
            }

            let html = `<table class="responsive-table projects-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="projectsTableBody">`;
            projects.forEach(proj => {
                const statusClass = proj.status.toLowerCase();
                html += `
                    <tr data-project-id="${proj.project_id}" data-user-id="${proj.user_id}">
                        <td><a href="../project.php?id=${proj.project_id}" target="_blank">${proj.title}</a></td>
                        <td><span class="view-student">${proj.student_name}</span></td>
                        <td class="status-cell">
                            <span class="status-pill ${statusClass}">
                                ${proj.status.charAt(0).toUpperCase() + proj.status.slice(1)}
                            </span>
                        </td>
                        <td>${new Date(proj.date_submitted).toLocaleDateString()}</td>
                        <td class="actions-cell">
                            ${statusClass === "pending" ? 
                                `<button class="action-btn approve-btn" data-status="approved" title="Approve"><i class="fas fa-check"></i></button>
                                 <button class="action-btn reject-btn" data-status="rejected" title="Reject"><i class="fas fa-times"></i></button>` : ""}
                            <button class="action-btn delete-project-btn" title="Delete" data-project-id="${proj.project_id}"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
            });
            html += `</tbody></table>`;
            projectsContainer.innerHTML = html;

        } catch (err) {
            stopLoading(projectsContainer);
            projectsContainer.innerHTML = `<p class="error-message">Error loading projects: ${err.message}</p>`;
        }
    };

    // --- 4.2 Status Update & Delete Delegation ---
    if (projectsContainer) {
        projectsContainer.addEventListener("click", async (e) => {
            const btn = e.target.closest(".approve-btn, .reject-btn, .delete-project-btn");
            if (!btn) return;
            
            const row = btn.closest("tr");
            const projectId = btn.dataset.projectId || row.dataset.projectId;
            
            let url = '';
            let body = { project_id: projectId, csrf_token: csrfToken };
            let actionType = '';
            
            if (btn.classList.contains('delete-project-btn')) {
                if (!confirm(`CONFIRM: Permanently delete project ${projectId}?`)) return;
                url = `${API_BASE}delete_project.php`;
                actionType = 'delete';
            } else {
                url = `${API_BASE}update_project_status.php`;
                body.status = btn.dataset.status;
                actionType = btn.dataset.status;
            }

            try {
                row.style.opacity = 0.5;
                const res = await fetch(url, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                
                if (data.status !== "success") throw new Error(data.message);

                if (actionType === 'delete') {
                    row.style.transform = 'scale(0.9)';
                    setTimeout(() => row.remove(), 300); 
                } else {
                    // Update Status Pill
                    const statusPill = row.querySelector(".status-pill");
                    if (statusPill) {
                        statusPill.textContent = actionType.charAt(0).toUpperCase() + actionType.slice(1);
                        statusPill.className = `status-pill ${actionType}`;
                    }
                    // Remove action buttons if status is no longer pending
                    if (actionType !== 'pending') {
                         row.querySelector('.actions-cell').innerHTML = `<a href="../project.php?id=${projectId}" target="_blank" class="action-btn view-btn" title="View"><i class="fas fa-eye"></i></a> <button class="action-btn delete-project-btn" title="Delete"><i class="fas fa-trash"></i></button>`;
                    }
                }
                
                showFeedback(`Project status updated.`, "success");

            } catch (err) {
                showFeedback(err.message, "error");
            } finally {
                 row.style.opacity = 1;
            }
        });

        // Search input with debouncing
        const projectsSearchInput = document.getElementById("projectsSearch");
        if (projectsSearchInput) {
            projectsSearchInput.addEventListener("input", () => {
                clearTimeout(projectSearchTimeout);
                projectSearchTimeout = setTimeout(() => loadProjects(projectsSearchInput.value.trim()), 400);
            });
        }
    }
});