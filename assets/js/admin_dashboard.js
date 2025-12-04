// admin_dashboard.js

document.addEventListener("DOMContentLoaded", () => {
    const table = document.querySelector(".projects-table");

    if (!table) return;

    table.addEventListener("click", async (e) => {
        const approveBtn = e.target.closest(".approve-btn");
        const rejectBtn = e.target.closest(".reject-btn");

        if (!approveBtn && !rejectBtn) return;

        const row = e.target.closest("tr");
        const projectId = row.dataset.projectId;
        const status = approveBtn ? "approved" : "rejected";

        if (!projectId) return;

        // Optional: Ask for confirmation
        const confirmAction = confirm(`Are you sure you want to ${status} this project?`);
        if (!confirmAction) return;

        try {
            // Disable buttons temporarily
            const buttons = row.querySelectorAll("button");
            buttons.forEach(btn => btn.disabled = true);

            const response = await fetch("../../api/projects/approve_project.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `project_id=${encodeURIComponent(projectId)}&status=${encodeURIComponent(status)}`
            });

            const data = await response.json();

            if (data.status === "success") {
                // Update status cell
                const statusCell = row.querySelector(".status");
                statusCell.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                statusCell.className = "status " + status;

                // Remove action buttons
                const actionsCell = row.querySelector(".actions");
                actionsCell.innerHTML = "<span>-</span>";

                alert(`Project ${status} successfully!`);
            } else {
                alert(`Error: ${data.message}`);
                // Re-enable buttons
                const buttons = row.querySelectorAll("button");
                buttons.forEach(btn => btn.disabled = false);
            }
        } catch (err) {
            alert("An unexpected error occurred. Please try again.");
            console.error(err);
            // Re-enable buttons
            const buttons = row.querySelectorAll("button");
            buttons.forEach(btn => btn.disabled = false);
        }
    });
});
