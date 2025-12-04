document.addEventListener('DOMContentLoaded', () => {
    // Optional: Add hover effects for cards dynamically (if needed)
    const cards = document.querySelectorAll('.stats-cards .card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.15)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 6px 15px rgba(0,0,0,0.08)';
        });
    });

    // Optional: Click project cards to open detailed project page
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            const title = card.querySelector('h3').textContent;
            const sanitizedTitle = encodeURIComponent(title);
            // Redirect to project.php with project title as query param
            window.location.href = `project.php?title=${sanitizedTitle}`;
        });
    });

    // Optional: Live tooltip for SDG tags
    const sdgTags = document.querySelectorAll('.sdg-tag');
    sdgTags.forEach(tag => {
        tag.addEventListener('mouseenter', () => {
            tag.style.backgroundColor = '#d4af37'; // Brighter gold on hover
        });
        tag.addEventListener('mouseleave', () => {
            tag.style.backgroundColor = '#bfa028'; // Default gold
        });
    });

    // Optional: Refresh dashboard stats without reloading page
    // Can be extended to fetch via AJAX for live updates
    const refreshStats = async () => {
        try {
            const response = await fetch('../api/projects/get_user_stats.php', {
                method: 'GET',
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status === 'success') {
                document.querySelector('.card.total p').textContent = data.stats.total_projects;
                document.querySelector('.card.approved p').textContent = data.stats.approved;
                document.querySelector('.card.rejected p').textContent = data.stats.rejected;
                document.querySelector('.card.pending p').textContent = data.stats.pending;
            }
        } catch (err) {
            console.error('Failed to refresh stats:', err);
        }
    };

    // Refresh stats every 30 seconds
    setInterval(refreshStats, 30000);
});
