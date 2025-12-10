document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================
    // 0. SINGLETON LOCK (Prevents Double Execution)
    // =========================================================
    if (window.myProjectsLoaded) return; 
    window.myProjectsLoaded = true;

    // =========================================================
    // 1. CINEMATIC NUMBERS (Count Up Effect)
    // =========================================================
    const animateCounters = () => {
        const counters = document.querySelectorAll('.stat-info h3');
        const speed = 2000; // Duration in ms

        counters.forEach(counter => {
            const target = +counter.innerText;
            const start = 0;
            const increment = target / (speed / 16); // 60 FPS

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
    animateCounters();

    // =========================================================
    // 2. ULTIMATE FILTERING ENGINE (With FLIP Animations)
    // =========================================================
    const filterBtns = document.querySelectorAll('.filter-btn');
    const searchInput = document.getElementById('projectSearch');
    const grid = document.getElementById('projectsGrid');
    const cards = Array.from(document.querySelectorAll('.project-card'));

    // State
    let currentFilter = 'all';
    let searchQuery = '';

    const filterProjects = () => {
        // 1. Record First positions (FLIP)
        cards.forEach(card => {
            card.dataset.top = card.getBoundingClientRect().top;
            card.dataset.left = card.getBoundingClientRect().left;
        });

        // 2. Apply Filters
        let visibleCount = 0;
        
        cards.forEach(card => {
            const status = card.dataset.status;
            const title = card.querySelector('.card-title').innerText.toLowerCase();
            
            const matchesFilter = currentFilter === 'all' || status === currentFilter;
            const matchesSearch = title.includes(searchQuery);

            if (matchesFilter && matchesSearch) {
                card.style.display = '';
                // Add staggered delay based on new index
                card.style.animationDelay = `${visibleCount * 0.05}s`;
                // Reset animation
                card.classList.remove('animate-in');
                void card.offsetWidth; // Trigger reflow
                card.classList.add('animate-in');
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // 3. Update UI Empty State
        const emptyState = document.querySelector('.empty-search-message');
        if (visibleCount === 0) {
            if (!emptyState) {
                const msg = document.createElement('div');
                msg.className = 'empty-search-message';
                msg.innerHTML = `<i class="fas fa-search"></i><p>No projects found matching your criteria.</p>`;
                if(grid) grid.appendChild(msg);
            }
        } else if (emptyState) {
            emptyState.remove();
        }
    };

    // Filter Button Click
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // UI Update
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Logic Update
            currentFilter = btn.dataset.filter;
            filterProjects();
        });
    });

    // Search Input (Debounced)
    let debounceTimer;
    if(searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                searchQuery = e.target.value.toLowerCase();
                filterProjects();
            }, 300);
        });
    }

    // =========================================================
    // 3. GOD MODE DELETE (Dynamic Glass Modal)
    // =========================================================
    const createConfirmModal = (onConfirm) => {
        // PREVENT DOUBLE MODALS
        if (document.querySelector('.modal-overlay.active')) return;

        // Create Modal HTML on the fly
        const modal = document.createElement('div');
        modal.className = 'modal-overlay active'; // Re-using your existing modal CSS
        modal.innerHTML = `
            <div class="modal-card">
                <div class="modal-content">
                    <div style="font-size: 3rem; color: var(--status-rejected-text); margin-bottom: 20px;">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <h3>Delete Project?</h3>
                    <p>This action cannot be undone. Are you sure you want to remove this masterpiece?</p>
                    <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center;">
                        <button class="btn-cancel" style="padding: 12px 30px; border: 1px solid #ccc; background: transparent; border-radius: 50px; cursor: pointer;">Cancel</button>
                        <button class="btn-confirm" style="padding: 12px 30px; background: var(--red-deep); color: white; border: none; border-radius: 50px; font-weight: 700; cursor: pointer; box-shadow: var(--shadow-gold);">Delete It</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Event Handling
        const close = () => {
            modal.style.opacity = '0';
            setTimeout(() => modal.remove(), 300);
        };

        // Click outside to close
        modal.addEventListener('click', (e) => {
            if (e.target === modal) close();
        });

        modal.querySelector('.btn-cancel').addEventListener('click', close);
        
        // Single-fire listener for confirm
        modal.querySelector('.btn-confirm').addEventListener('click', () => {
            onConfirm();
            close();
        }, { once: true }); // Important: Ensures it only fires once
    };

    // Attach Delete Listeners (Using Delegation for dynamic items)
    if(grid) {
        grid.addEventListener('click', async (e) => {
            const btn = e.target.closest('.action-btn.delete');
            if (!btn) return;

            e.stopPropagation(); // Stop event bubbling

            const projectId = btn.dataset.id;
            const csrfToken = btn.dataset.token;
            const card = btn.closest('.project-card');

            createConfirmModal(async () => {
                try {
                    // Optimistic UI Update (Hide it immediately)
                    card.style.transform = 'scale(0.9) opacity(0)';
                    setTimeout(() => card.style.display = 'none', 400);

                    // Send Request
                    const response = await fetch('../api/projects/delete_my_project.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: projectId, csrf_token: csrfToken })
                    });

                    const result = await response.json();

                    if (!result.success) {
                        // Revert if failed
                        card.style.display = '';
                        setTimeout(() => card.style.transform = 'none', 100);
                        alert(result.message || "Delete failed.");
                    } else {
                        // Fully remove from DOM
                        setTimeout(() => card.remove(), 400);
                        // Update Stats Counters Decrement
                        updateStatsOnDelete(card.dataset.status);
                    }

                } catch (error) {
                    console.error("Delete error:", error);
                    card.style.display = '';
                    alert("Network error.");
                }
            });
        });
    }

    const updateStatsOnDelete = (status) => {
        // Helper to decrement numbers on the dashboard without reload
        const totalEl = document.querySelector('.stat-card:nth-child(1) h3');
        if(totalEl) totalEl.innerText = Math.max(0, +totalEl.innerText - 1);

        if(status === 'approved') {
            const appEl = document.querySelector('.stat-card:nth-child(2) h3');
            if(appEl) appEl.innerText = Math.max(0, +appEl.innerText - 1);
        } else if (status === 'pending') {
            const penEl = document.querySelector('.stat-card:nth-child(3) h3');
            if(penEl) penEl.innerText = Math.max(0, +penEl.innerText - 1);
        }
    };

    // =========================================================
    // 4. PARALLAX MOUSE ENGINE
    // =========================================================
    document.addEventListener('mousemove', (e) => {
        const orbs = document.querySelectorAll('.gold-orb');
        if(!orbs.length) return;

        const x = (window.innerWidth - e.pageX * 2) / 100;
        const y = (window.innerHeight - e.pageY * 2) / 100;

        orbs.forEach((orb, index) => {
            const speed = index === 0 ? 1 : -1; // Opposite directions
            orb.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
        });
    });

    // =========================================================
    // 5. INIT 3D TILT
    // =========================================================
    if (typeof VanillaTilt !== "undefined") {
        VanillaTilt.init(document.querySelectorAll("[data-tilt]"), {
            max: 5,
            speed: 1000,
            scale: 1.02,
            glare: true,
            "max-glare": 0.1,
        });
    }
});
