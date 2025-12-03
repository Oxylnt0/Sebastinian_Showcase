// index.js

document.addEventListener('DOMContentLoaded', () => {
    loadSDGs();
    fetchProjects();

    // Event listeners
    document.getElementById('filter-sdg').addEventListener('change', fetchProjects);
    document.getElementById('search-title').addEventListener('input', debounce(fetchProjects, 500));
});

/**
 * Fetch projects from the API with optional filters
 */
async function fetchProjects() {
    const sdg = document.getElementById('filter-sdg').value;
    const search = document.getElementById('search-title').value.trim();

    const params = new URLSearchParams();
    if (sdg) params.append('sdg_id', sdg);
    if (search) params.append('search', search);

    try {
        const res = await fetch(`../api/projects/get_projects.php?${params.toString()}`);
        const data = await res.json();

        if (data.status === 'success') {
            renderProjects(data.data);
        } else {
            showMessage(data.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showMessage('Failed to fetch projects.', 'error');
    }
}

/**
 * Render projects to the grid
 */
function renderProjects(projects) {
    const grid = document.getElementById('projects-grid');
    grid.innerHTML = '';

    if (!projects.length) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;">No projects found.</p>';
        return;
    }

    projects.forEach(project => {
        const card = document.createElement('div');
        card.className = 'project-card';
        card.innerHTML = `
            ${project.image ? `<img src="${project.image}" alt="${project.title}">` : ''}
            <div class="card-content">
                <h3>${escapeHTML(project.title)}</h3>
                <p>${escapeHTML(project.description.substring(0, 150))}${project.description.length > 150 ? '...' : ''}</p>
                ${project.sdg.sdg_name ? `<span class="sdg-tag">${escapeHTML(project.sdg.sdg_name)}</span>` : ''}
            </div>
        `;
        grid.appendChild(card);
    });
}

/**
 * Load SDGs dynamically from existing projects
 */
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
    } catch (err) {
        console.error(err);
    }
}

/**
 * Debounce function to limit API calls
 */
function debounce(func, delay) {
    let timer;
    return function () {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, arguments), delay);
    };
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHTML(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Display messages to user
 */
function showMessage(message, type = 'info') {
    const existing = document.querySelector('.message-box');
    if (existing) existing.remove();

    const div = document.createElement('div');
    div.className = `message-box ${type}`;
    div.textContent = message;
    document.body.prepend(div);

    setTimeout(() => div.remove(), 4000);
}
