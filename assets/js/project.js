// =============================
// Ultimate JS for Project Page
// =============================
document.addEventListener("DOMContentLoaded", () => {

    const projectId = document.querySelector("#comment-form")?.dataset.projectId;
    const commentsList = document.getElementById("comments-list");
    const commentForm = document.getElementById("comment-form");
    const commentTextarea = document.getElementById("comment");
    const commentMessage = document.getElementById("comment-message");
    const likeBtn = document.getElementById("like-btn");
    const likeCountElem = document.getElementById("like-count");
    const deleteProjectBtn = document.getElementById("delete-project-btn");
    const viewCountElem = document.getElementById("view-count");
    const downloadBtn = document.getElementById("download-file-btn");
    const downloadCountElem = document.getElementById("download-count");

    // -------------------------
    // Increment view counter
    // -------------------------
    if (viewCountElem) {
        fetch("../api/projects/increment_view.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ project_id: projectId })
        }).then(res => res.json())
          .then(data => { if (data.status === "success") viewCountElem.textContent = data.views; });
    }

    // -------------------------
    // Like / Unlike Project
    // -------------------------
    if (likeBtn && likeCountElem) {
        likeBtn.addEventListener("click", () => {
            fetch("../api/projects/like_project.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ project_id: projectId })
            }).then(res => res.json())
              .then(data => {
                  if (data.status === "success") {
                      likeCountElem.textContent = data.likes;
                      likeBtn.classList.toggle("liked", data.liked);
                      likeBtn.style.transform = "scale(1.3)";
                      setTimeout(() => likeBtn.style.transform = "scale(1)", 200);
                  }
              }).catch(console.error);
        });
    }

    // -------------------------
    // Delete Project
    // -------------------------
    if (deleteProjectBtn) {
        deleteProjectBtn.addEventListener("click", () => {
            if (!confirm("Are you sure you want to delete this project?")) return;
            fetch("../api/projects/delete_project.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ project_id: projectId })
            }).then(res => res.json())
              .then(data => {
                  if (data.status === "success") {
                      alert("Project deleted!");
                      window.location.href = "dashboard.php";
                  } else alert(data.message || "Failed to delete project.");
              }).catch(() => alert("Server error."));
        });
    }

    // -------------------------
    // Download Counter
    // -------------------------
    if (downloadBtn && downloadCountElem) {
        downloadBtn.addEventListener("click", () => {
            fetch("../api/projects/download_file.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ project_id: projectId })
            }).then(res => res.json())
              .then(data => { if (data.status === "success") downloadCountElem.textContent = `Downloaded: ${data.downloads}`; })
              .catch(console.error);
        });
    }

    // -------------------------
    // Submit Comment
    // -------------------------
    if (commentForm) {
        commentForm.addEventListener("submit", e => {
            e.preventDefault();
            const comment = commentTextarea.value.trim();
            if (!comment) return;
            commentMessage.textContent = "Posting...";
            fetch("../api/projects/add_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ project_id: projectId, comment })
            }).then(res => res.json())
              .then(data => {
                  if (data.status === "success") {
                      addCommentToDOM(data.comment);
                      commentTextarea.value = "";
                      commentMessage.textContent = "";
                  } else commentMessage.textContent = data.message || "Failed to post comment.";
              }).catch(() => commentMessage.textContent = "Server error.");
        });
    }

    // -------------------------
    // Add Comment / Reply to DOM
    // -------------------------
    function addCommentToDOM(comment, parentElem = null) {
        const div = document.createElement("div");
        div.className = "comment";
        div.dataset.commentId = comment.comment_id;
        div.innerHTML = `
            <p><strong>${escapeHTML(comment.full_name)}:</strong> ${escapeHTML(comment.comment)}</p>
            <span class="comment-date">${comment.time_ago}</span>
            ${comment.is_owner ? '<button class="edit-comment-btn">Edit</button><button class="delete-comment-btn">Delete</button>' : ''}
            <button class="like-comment-btn">👍 Like</button>
            <div class="reply-section">
                <input type="text" class="reply-input" placeholder="Reply..." />
                <button class="reply-btn">Reply</button>
            </div>
            <div class="nested-replies"></div>
        `;
        if (parentElem) {
            parentElem.querySelector(".nested-replies").appendChild(div);
        } else {
            commentsList.appendChild(div);
        }
        div.scrollIntoView({ behavior: "smooth" });
    }

    // -------------------------
    // Escape HTML
    // -------------------------
    function escapeHTML(str) {
        return str.replace(/[&<>"']/g, m => ({ "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[m]));
    }

    // -------------------------
    // Real-time Comment Polling
    // -------------------------
    setInterval(() => {
        fetch(`../api/projects/get_comments.php?project_id=${projectId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    commentsList.innerHTML = "";
                    data.comments.forEach(c => addCommentToDOM(c));
                }
            }).catch(console.error);
    }, 5000);

    // -------------------------
    // Event Delegation for Comments (Edit/Delete/Reply/Like)
    // -------------------------
    commentsList.addEventListener("click", e => {
        const commentDiv = e.target.closest(".comment");
        if (!commentDiv) return;
        const commentId = commentDiv.dataset.commentId;

        // Delete Comment
        if (e.target.classList.contains("delete-comment-btn")) {
            if (!confirm("Delete this comment?")) return;
            fetch("../api/projects/delete_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ comment_id: commentId })
            }).then(res => res.json())
              .then(data => { if (data.status === "success") commentDiv.remove(); })
              .catch(console.error);
        }

        // Edit Comment
        if (e.target.classList.contains("edit-comment-btn")) {
            const p = commentDiv.querySelector("p");
            const oldText = p.textContent.split(": ")[1];
            const input = document.createElement("textarea");
            input.value = oldText;
            p.replaceWith(input);
            const saveBtn = document.createElement("button");
            saveBtn.textContent = "Save";
            input.after(saveBtn);
            saveBtn.addEventListener("click", () => {
                const newComment = input.value.trim();
                if (!newComment) return;
                fetch("../api/projects/edit_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ comment_id: commentId, comment: newComment })
                }).then(res => res.json())
                  .then(data => {
                      if (data.status === "success") {
                          input.replaceWith(p);
                          p.textContent = `${commentDiv.querySelector("strong").textContent}: ${newComment}`;
                          saveBtn.remove();
                      }
                  }).catch(console.error);
            });
        }

        // Reply to Comment
        if (e.target.classList.contains("reply-btn")) {
            const replyInput = commentDiv.querySelector(".reply-input");
            const replyText = replyInput.value.trim();
            if (!replyText) return;
            fetch("../api/projects/add_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ project_id: projectId, comment: replyText, parent_id: commentId })
            }).then(res => res.json())
              .then(data => {
                  if (data.status === "success") {
                      addCommentToDOM(data.comment, commentDiv);
                      replyInput.value = "";
                  }
              }).catch(console.error);
        }

        // Like Comment (optional)
        if (e.target.classList.contains("like-comment-btn")) {
            fetch("../api/projects/like_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ comment_id: commentId })
            }).then(res => res.json())
              .then(data => { if (data.status === "success") e.target.textContent = `👍 Like (${data.likes})`; })
              .catch(console.error);
        }
    });

});
