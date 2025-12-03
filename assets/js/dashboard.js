// assets/js/dashboard.js

document.addEventListener("DOMContentLoaded", () => {
    // Initialize dashboard
    loadSDGs();
    fetchProjects();
});

// Fetch projects with optional filters
async function fetchProjects() {
    const sdg = document.getElementById('filter-sdg').value;
    const status = document.getElementById('filter-status').value;
    const search = document.getElementById('search-title').value;

    const params = new URLSearchParams();
    if (sdg) params.append('sdg_id', sdg);
    if (status) params.append('status', status);
    if (search) params.append('search', search);

    try {
        const res = await fetch(`../api/projects/get_projects.php?${params.toString()}`);
        const data = await res.json();

        if (data.status === 'success') {
            renderProjects(data.data);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showAlert('Failed to fetch projects. Please try again.', 'error');
    }
}

// Render project cards in the grid
function renderProjects(projects) {
    const grid = document.getElementById('projects-grid');
    grid.innerHTML = '';

    if (projects.length === 0) {
        grid.innerHTML = `<p style="grid-column:1/-1;text-align:center;font-weight:bold;color:#B71C1C;">No projects found.</p>`;
        return;
    }

    projects.forEach(project => {
        const card = document.createElement('div');
        card.className = 'project-card';
        card.innerHTML = `
            ${project.image ? `<img src="${project.image}" alt="${project.title}">` : ''}
            <div class="card-content">
                <h3>${escapeHtml(project.title)}</h3>
                <p>${truncateText(project.description, 150)}</p>
                ${project.sdg.sdg_name ? `<span class="sdg-tag">${project.sdg.sdg_name}</span>` : ''}
            </div>
        `;
        grid.appendChild(card);
    });
}

// Load SDG filter dynamically
async function loadSDGs() {
    try {
        const res = await fetch('../api/projects/get_projects.php');
        const data = await res.json();
        if (data.status === 'success') {
            const sdgSet = new Set();
            data.data.forEach(p => {
                if (p.sdg.sdg_id) sdgSet.add(JSON.stringify(p.sdg));
            });

            const select = document.getElementById('filter-sdg');
            sdgSet.forEach(sdgStr => {
                const sdg = JSON.parse(sdgStr);
                const option = document.createElement('option');
                option.value = sdg.sdg_id;
                option.textContent = sdg.sdg_name;
                select.appendChild(option);
            });
        }
    } catch(err) {
        console.error('Failed to load SDGs:', err);
    }
}

// Logout functionality
async function logout(event) {
    event.preventDefault();
    try {
        const res = await fetch('../api/auth/logout.php');
        const data = await res.json();
        if (data.status === 'success') {
            window.location.href = 'login.php';
        } else {
            showAlert('Logout failed.', 'error');
        }
    } catch(err) {
        console.error(err);
        showAlert('Logout failed.', 'error');
    }
}

// Utility: truncate text with ellipsis
function truncateText(text, maxLength) {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

// Utility: escape HTML to prevent XSS
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Utility: show alert (temporary message)
function showAlert(message, type = 'info', duration = 3000) {
    const alertBox = document.createElement('div');
    alertBox.textContent = message;
    alertBox.style.position = 'fixed';
    alertBox.style.bottom = '20px';
    alertBox.style.right = '20px';
    alertBox.style.padding = '1rem 1.5rem';
    alertBox.style.borderRadius = '10px';
    alertBox.style.color = '#fff';
    alertBox.style.fontWeight = 'bold';
    alertBox.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
    alertBox.style.zIndex = 9999;

    // Red for error, gold for success/info
    if (type === 'error') {
        alertBox.style.background = '#B71C1C';
    } else {
        alertBox.style.background = '#FFD700';
        alertBox.style.color = '#212121';
    }

    document.body.appendChild(alertBox);

    setTimeout(() => {
        alertBox.remove();
    }, duration);
}

// Attach logout event dynamically if logout link exists
const logoutLink = document.querySelector('a[href$="logout.php"]');
if (logoutLink) {
    logoutLink.addEventListener('click', logout);
}
