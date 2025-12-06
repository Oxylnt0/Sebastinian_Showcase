/* =========================================== */
/* Premium Admin Dashboard JS – Sebastinian Showcase */
/* Fully interactive, UX-focused, modern, AJAX  */
/* =========================================== */

document.addEventListener("DOMContentLoaded", () => {

    // -----------------------------
    // Create floating feedback element
    // -----------------------------
    const feedback = document.createElement("div");
    feedback.className = "feedback";
    document.body.prepend(feedback);

    const showFeedback = (message, type = "error") => {
        feedback.textContent = message;
        feedback.className = `feedback ${type}`;
        feedback.style.opacity = 1;
        feedback.style.transform = "translateY(0)";
        setTimeout(() => {
            feedback.style.opacity = 0;
            feedback.style.transform = "translateY(-10px)";
        }, 4000);
    };

    // -----------------------------
    // Update summary cards dynamically
    // -----------------------------
    const updateSummaryCards = () => {
        const cards = {
            total: document.querySelector(".total-projects p"),
            approved: document.querySelector(".approved-projects p"),
            pending: document.querySelector(".pending-projects p")
        };

        const rows = Array.from(document.querySelectorAll(".projects-table tbody tr"));
        const counts = { approved: 0, pending: 0 };

        rows.forEach(row => {
            const status = row.querySelector(".status").textContent.toLowerCase();
            if (counts[status] !== undefined) counts[status]++;
        });

        cards.total.textContent = rows.length;
        cards.approved.textContent = counts.approved;
        cards.pending.textContent = counts.pending;
    };

    // -----------------------------
    // Handle approve/reject actions
    // -----------------------------
    const handleAction = async (button) => {
        const row = button.closest("tr");
        const projectId = row.dataset.projectId;
        const status = button.dataset.status;

        button.disabled = true;

        try {
            const response = await fetch("../api/admin/update_project_status.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ project_id: projectId, status })
            });

            const result = await response.json();

            if (result.status === "success") {
                // Remove row if rejected
                if (status === "rejected") {
                    row.remove();
                } else {
                    const statusCell = row.querySelector(".status");
                    statusCell.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusCell.className = "status " + status;
                    row.querySelector(".actions").innerHTML = "<span>-</span>";
                }

                showFeedback(result.message, "success");
                updateSummaryCards();
            } else {
                row.classList.add("shake");
                setTimeout(() => row.classList.remove("shake"), 500);
                showFeedback(result.message || "Action failed", "error");
            }
        } catch (error) {
            row.classList.add("shake");
            setTimeout(() => row.classList.remove("shake"), 500);
            showFeedback("Server error: " + error.message, "error");
        } finally {
            button.disabled = false;
        }
    };

    // -----------------------------
    // Attach event listeners to approve/reject buttons
    // -----------------------------
    const attachButtons = () => {
        document.querySelectorAll(".approve-btn, .reject-btn").forEach(btn => {
            // Clone button to remove old listeners
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener("click", () => handleAction(newBtn));
        });
    };

    attachButtons();
    updateSummaryCards();

    // -----------------------------
    // Add Admin button functionality
    // -----------------------------
    const addAdminBtn = document.getElementById("addAdminBtn");
    if (addAdminBtn) {
        addAdminBtn.addEventListener("click", () => {
            window.location.href = "admin_register.php";
        });
    }
});
