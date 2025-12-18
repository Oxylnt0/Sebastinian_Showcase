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

// --- IMPROVED DELETE LOGIC ---
// We use a single, clean event listener on the grid
grid.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('.action-btn.delete');
    if (!deleteBtn) return; // Exit if we didn't click a delete button

    const id = deleteBtn.dataset.id;
    
    // Check if we are already deleting to prevent double-clicks
    if (deleteBtn.classList.contains('is-processing')) return;

    const executeDelete = () => performDelete(id, deleteBtn);

    // Single confirmation check
    if (typeof createConfirmModal === 'function') {
        createConfirmModal(executeDelete);
    } else {
        if (confirm("Are you sure you want to permanently delete this research?")) {
            executeDelete();
        }
    }
});

// --- EDIT LOGIC ---
grid.addEventListener('click', (e) => {
    // Check if the clicked element (or its parent) is the edit button
    const editBtn = e.target.closest('.action-btn.edit');
    if (!editBtn) return;

    // Get the project ID from the data-id attribute
    const id = editBtn.dataset.id;

    // Redirect the user to the edit page with the project ID
    window.location.href = `edit_project.php?id=${id}`;
});

const performDelete = async (id, btn) => {
    // Add a processing state to the button
    btn.classList.add('is-processing');
    const card = btn.closest('article');

    try {
        const response = await fetch('../api/projects/delete_my_project.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id: id, 
                csrf_token: typeof GLOBAL_CSRF_TOKEN !== 'undefined' ? GLOBAL_CSRF_TOKEN : '' 
            }) 
        });

        // First check if the response is actually OK
        if (!response.ok) throw new Error('Network response was not ok');

        const res = await response.json();

        if (res.success) {
            // 1. Visually remove the card immediately
            card.style.pointerEvents = 'none';
            card.style.transform = 'scale(0.9) translateY(20px)';
            card.style.opacity = '0';
            card.style.transition = 'all 0.4s ease';

            setTimeout(() => {
                card.remove();
                
                // 2. Check if grid is empty without re-fetching from database
                const remainingCards = document.querySelectorAll('.project-card');
                if (remainingCards.length === 0) {
                    if (noResults) noResults.style.display = 'block';
                }
            }, 400);

            console.log("Thesis removed successfully.");
        } else {
            // This catches the "Project Not Found" from the PHP side
            alert("Note: " + res.message);
            btn.classList.remove('is-processing');
        }
    } catch (err) {
        console.error("Deletion Error:", err);
        alert("System error. The project may have already been deleted.");
        btn.classList.remove('is-processing');
    }
};
});