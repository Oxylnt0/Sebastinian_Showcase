document.addEventListener("DOMContentLoaded", () => {
    const commentForm = document.getElementById("comment-form");
    const commentList = document.getElementById("comments-list");
    const commentMessage = document.getElementById("comment-message");

    if (!commentForm) return; // Exit if comment form is not present

    commentForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const projectId = commentForm.dataset.projectId;
        const commentText = commentForm.comment.value.trim();

        if (!commentText) {
            commentMessage.textContent = "Comment cannot be empty.";
            commentMessage.style.color = "#B22222";
            return;
        }

        commentMessage.textContent = "Posting comment...";
        commentMessage.style.color = "#555";

        // Prepare FormData for AJAX
        const formData = new FormData();
        formData.append("project_id", projectId);
        formData.append("comment", commentText);

        fetch("../api/utils/add_comment.php", {
            method: "POST",
            body: formData,
            credentials: "same-origin"
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                // Clear textarea
                commentForm.comment.value = "";

                // Add new comment dynamically
                const commentDiv = document.createElement("div");
                commentDiv.classList.add("comment");

                const now = new Date();
                const formattedDate = now.toLocaleString("en-US", {
                    month: "short",
                    day: "numeric",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit"
                });

                commentDiv.innerHTML = `
                    <p><strong>${data.user_name}:</strong> ${escapeHtml(commentText)}</p>
                    <span class="comment-date">${formattedDate}</span>
                `;
                commentList.appendChild(commentDiv);

                commentMessage.textContent = "Comment posted successfully!";
                commentMessage.style.color = "#28a745"; // green
            } else {
                commentMessage.textContent = data.message || "Failed to post comment.";
                commentMessage.style.color = "#B22222"; // red
            }
        })
        .catch((error) => {
            console.error("Error posting comment:", error);
            commentMessage.textContent = "An error occurred. Try again.";
            commentMessage.style.color = "#B22222";
        });
    });

    // Function to safely escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
});
