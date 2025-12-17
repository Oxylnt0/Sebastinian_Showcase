document.addEventListener("DOMContentLoaded", () => {
    
    // ==========================================
    // 1. SEARCH & FILTER ENGINE (My Projects)
    // ==========================================
    const searchInput = document.getElementById('mySearchInput');
    const filterStatus = document.getElementById('filterStatus');
    const filterType  = document.getElementById('filterType');
    // Removed filterDept
    const filterYear  = document.getElementById('filterYear');
    const sortOrder   = document.getElementById('sortOrder');
    const grid        = document.getElementById('projectsGrid');
    const noResults   = document.getElementById('noResults');

    let debounceTimer;
    const debounce = (func, delay) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(func, delay);
    };

    // --- FETCH DATA ---
    const fetchMyProjects = async () => {
        if (!grid) return;
        
        grid.style.opacity = '0.5'; 

        const params = new URLSearchParams({
            q: searchInput.value,
            status: filterStatus.value,
            type: filterType.value,
            // Removed dept: filterDept.value
            year: filterYear.value,
            sort: sortOrder.value
        });

        try {
            // Note: The API still accepts 'dept' but defaults to 'all' if missing, so no API change needed.
            const response = await fetch(`../api/projects/search_my_projects.php?${params}`);
            const result = await response.json();

            if (result.success) {
                renderMyProjects(result.data);
            } else {
                console.error("Error:", result.message);
            }
        } catch (error) {
            console.error("Network Error:", error);
        } finally {
            grid.style.opacity = '1';
        }
    };

    // --- RENDER CARDS ---
    const renderMyProjects = (projects) => {
        grid.innerHTML = '';
        
        if (projects.length === 0) {
            noResults.style.display = 'block';
            return;
        }
        noResults.style.display = 'none';

        projects.forEach(p => {
            const dateStr = new Date(p.date_submitted).toLocaleDateString("en-US", { year: 'numeric', month: 'short', day: 'numeric' });
            const imagePath = p.image ? `../uploads/project_images/${p.image}` : null;
            const filePath = p.file ? `../uploads/project_files/${p.file}` : '#';
            const status = p.status || 'draft';
            const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
            
            const article = document.createElement('article');
            article.className = 'project-card glass-card';
            article.dataset.status = status;
            article.setAttribute('data-tilt', '');

            let mediaHtml = imagePath 
                ? `<img src="${imagePath}" alt="Cover" loading="lazy">` 
                : `<div class="placeholder-art"><i class="fas fa-book-open"></i></div>`;

            let deptBadge = p.department 
                ? `<span class="sdg-pill" title="${p.department}"><i class="fas fa-university"></i> ${p.department}</span>` 
                : '';

            article.innerHTML = `
                <div class="card-media">
                    ${mediaHtml}
                    <div class="card-overlay">
                        <a href="project.php?id=${p.project_id}" class="btn-view">Read Thesis <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <span class="status-badge ${status}">${statusLabel}</span>
                </div>
                <div class="card-content">
                    <div class="card-meta">
                        <span class="date"><i class="far fa-calendar-alt"></i> ${dateStr}</span>
                        ${deptBadge}
                    </div>
                    <h3 class="card-title"><a href="project.php?id=${p.project_id}">${p.title}</a></h3>
                    <p class="card-excerpt">${p.description.substring(0, 90)}...</p>
                    <div class="card-footer">
                        <a href="${filePath}" class="action-link download" download><i class="fas fa-file-pdf"></i></a>
                        <div class="footer-actions">
                            <button class="action-btn edit" data-id="${p.project_id}"><i class="fas fa-pen"></i></button>
                            <button class="action-btn delete" data-id="${p.project_id}" data-token="${document.body.dataset.csrf}"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `;
            grid.appendChild(article);
        });

        if (typeof VanillaTilt !== "undefined") {
            VanillaTilt.init(document.querySelectorAll("[data-tilt]"));
        }
    };

    // --- EVENT LISTENERS ---
    if (searchInput) {
        searchInput.addEventListener('input', () => debounce(fetchMyProjects, 500));
        filterStatus.addEventListener('change', fetchMyProjects);
        filterType.addEventListener('change', fetchMyProjects);
        // Removed filterDept listener
        filterYear.addEventListener('change', fetchMyProjects);
        sortOrder.addEventListener('change', fetchMyProjects);
        
        fetchMyProjects();
    }

    // --- DELETE LOGIC (Unchanged) ---
    grid.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('.action-btn.delete');
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            if(typeof createConfirmModal === 'function') {
                createConfirmModal(() => performDelete(id, deleteBtn));
            } else {
                if(confirm("Are you sure you want to delete this?")) performDelete(id, deleteBtn);
            }
        }
    });

    const performDelete = async (id, btn) => {
        try {
            const response = await fetch('../api/projects/delete_my_project.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id }) 
            });
            const res = await response.json();
            if(res.success) {
                btn.closest('article').remove();
            } else {
                alert("Failed: " + res.message);
            }
        } catch (err) {
            console.error(err);
        }
    };
});