// ===============================
// project.js - Ultimate Version
// ===============================
document.addEventListener("DOMContentLoaded", () => {
    // -----------------------
    // Constants & DOM elements
    // -----------------------
    const commentForm = document.getElementById("comment-form");
    const commentsList = document.getElementById("comments-list");
    const projectId = Number(window.projectId || commentForm?.dataset.projectId || 0);
    const sessionUserId = Number(window.sessionUserId || 0);
    const MAX_DEPTH = 3;
    let lastCommentSent = "";

    if (!projectId || !commentsList) return console.error("Project ID or comments container missing");

    // -----------------------
    // Helper: AJAX
    // -----------------------
    const ajax = async (url, method = "POST", data = {}) => {
        try {
            const options = { method };
            if (["POST", "PUT"].includes(method)) {
                options.headers = { "Content-Type": "application/json" };
                options.body = JSON.stringify(data);
            }
            const res = await fetch(url, options);
            return await res.json();
        } catch (err) {
            console.error("AJAX Error:", err);
            return { status: "error", message: "Network error" };
        }
    };

    // -----------------------
    // Helper: Confirm
    // -----------------------
    const confirmAction = (msg) => window.confirm(msg);

    // -----------------------
    // Helper: Fade-in
    // -----------------------
    const fadeIn = (el) => requestAnimationFrame(() => el.style.opacity = 1);

    // -----------------------
    // Helper: Format numbers
    // -----------------------
    const formatCount = (n) => (n > 999 ? (n / 1000).toFixed(1) + "k" : n);

    // -----------------------
    // Update counters in DOM
    // -----------------------
    const updateLikeCount = (count) => {
        const el = document.getElementById("like-count");
        if (el) el.textContent = formatCount(count);
    };
    const updateDownloadCount = (count) => {
        const el = document.getElementById("download-count");
        if (el) el.textContent = `Downloaded: ${formatCount(count)}`;
    };
    const updateCommentCount = () => {
        const countEl = document.querySelector(".comments-section h2");
        if (countEl) countEl.textContent = `Comments (${commentsList.querySelectorAll(".comment").length})`;
    };

    // -----------------------
    // Render comment HTML
    // -----------------------
    const createCommentHTML = (comment, depth = 0) => {
        const own = comment.user_id === sessionUserId ? "own-comment" : "";
        const indent = depth * 20;
        return `
        <div class="comment ${own}" data-comment-id="${comment.comment_id}" 
             style="opacity:0; transition:opacity 0.4s; margin-left:${indent}px;">
            <p><strong>${comment.full_name}:</strong> ${comment.comment}</p>
            <span class="comment-date">${comment.time_ago}</span>
            <span class="comment-likes">👍 <span class="comment-like-count">${comment.likes}</span></span>
            ${comment.user_id === sessionUserId ? `
                <button class="edit-comment-btn">Edit</button>
                <button class="delete-comment-btn">Delete</button>` : ""}
            ${comment.user_id !== sessionUserId && sessionUserId ? `<button class="like-comment-btn">👍 Like</button>` : ""}
            ${depth < MAX_DEPTH ? `
            <div class="reply-section">
                <input type="text" class="reply-input" placeholder="Reply..." />
                <button class="reply-btn">Reply</button>
            </div>` : ""}
            <div class="nested-replies" data-parent-id="${comment.comment_id}"></div>
        </div>`;
    };

    // -----------------------
    // Recursively render comments
    // -----------------------
    const renderComments = (comments, container, depth = 0) => {
        comments.forEach(comment => {
            if (!container) return;
            const existing = container.querySelector(`[data-comment-id="${comment.comment_id}"]`);
            if (existing) existing.remove();

            container.insertAdjacentHTML("beforeend", createCommentHTML(comment, depth));
            const newEl = container.querySelector(`[data-comment-id="${comment.comment_id}"]`);
            if (newEl) fadeIn(newEl);

            // Render nested replies
            if (comment.replies?.length) {
                const nestedDiv = newEl.querySelector(".nested-replies") || (() => {
                    const d = document.createElement("div");
                    d.classList.add("nested-replies");
                    d.dataset.parentId = comment.comment_id;
                    newEl.appendChild(d);
                    return d;
                })();

                if (depth + 1 >= MAX_DEPTH) {
                    const btn = document.createElement("button");
                    btn.textContent = `View ${comment.replies.length} more replies`;
                    btn.classList.add("view-more-replies");
                    btn.addEventListener("click", () => {
                        renderComments(comment.replies, nestedDiv, depth + 1);
                        btn.remove();
                        updateCommentCount();
                    });
                    nestedDiv.appendChild(btn);
                } else renderComments(comment.replies, nestedDiv, depth + 1);
            }
        });
        updateCommentCount();
    };

    // -----------------------
    // Load comments from server
    // -----------------------
    const loadComments = async () => {
        const data = await ajax(`../api/projects/get_comments.php?project_id=${projectId}`, "GET");
        if (data.status === "success" && Array.isArray(data.data?.comments)) {
            commentsList.innerHTML = "";
            renderComments(data.data.comments, commentsList);
        }
    };

    loadComments();

    // -----------------------
    // Like project
    // -----------------------
    const likeBtn = document.getElementById("like-btn");
    const likeCountEl = likeBtn?.querySelector("#like-count");

    if (likeBtn && likeCountEl && !likeBtn.dataset.bound) {
        likeBtn.dataset.bound = true;

        likeBtn.addEventListener("click", async function () {
            this.disabled = true;

            try {
                const data = await ajax("../api/projects/like_project.php", "POST", { project_id: projectId });

                if (data.status === "success") {
                    // Toggle heart visual
                    likeBtn.classList.toggle("liked", data.data.user_liked);
                    likeBtn.setAttribute("aria-pressed", data.data.user_liked ? "true" : "false");

                    // Update like count immediately
                    const count = data.data.like_count ?? 0;
                    likeCountEl.textContent = count;
                } else {
                    alert(data.message || "Something went wrong.");
                }
            } catch (err) {
                console.error(err);
                alert("Network error");
            } finally {
                this.disabled = false;
            }
        });
    }


    // -----------------------
    // Log download
    // -----------------------
    document.getElementById("download-file-btn")?.addEventListener("click", async function () {
        this.disabled = true;
        const data = await ajax("../api/projects/log_download.php", "POST", { project_id: projectId });
        this.disabled = false;
        if (data.status === "success") updateDownloadCount(data.download_count);
    });

    // -----------------------
    // Submit new top-level comment
    // -----------------------
    if (commentForm && !commentForm.dataset.bound) {
        commentForm.dataset.bound = true;
        commentForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const textarea = commentForm.querySelector("#comment");
            const msgDiv = commentForm.querySelector("#comment-message");
            const text = textarea.value.trim();
            if (!text) return;

            if (text === lastCommentSent) {
                msgDiv.textContent = "Please wait before sending the same comment again.";
                msgDiv.className = "comment-message error";
                return;
            }

            lastCommentSent = text;
            const btn = commentForm.querySelector("button[type=submit]");
            btn.disabled = true;

            const data = await ajax("../api/projects/add_comment.php", "POST", { project_id: projectId, comment: text });
            btn.disabled = false;

            if (data.status === "success" && data.data?.comment) {
                renderComments([data.data.comment], commentsList);
                textarea.value = "";
                msgDiv.textContent = "Comment posted!";
                msgDiv.className = "comment-message success";
                setTimeout(() => { msgDiv.textContent = ""; msgDiv.className = "comment-message"; }, 2000);
            } else {
                msgDiv.textContent = data.message || "Something went wrong.";
                msgDiv.className = "comment-message error";
            }

            setTimeout(() => lastCommentSent = "", 2000);
        });
    }

    // -----------------------
    // Delegate comment actions (like, edit, delete, reply)
    // -----------------------
    if (!commentsList.dataset.bound) {
        commentsList.dataset.bound = true;
        commentsList.addEventListener("click", async (e) => {
            const target = e.target;
            const commentEl = target.closest(".comment");
            if (!commentEl) return;
            const commentId = commentEl.dataset.commentId;

            // Like comment
            if (target.classList.contains("like-comment-btn")) {
                target.disabled = true;
                const data = await ajax("../api/projects/toggle_comment_like.php", "POST", { comment_id: commentId });
                target.disabled = false;
                if (data.status === "success") commentEl.querySelector(".comment-like-count").textContent = data.like_count;
                else alert(data.message);
            }

            // Delete comment
            if (target.classList.contains("delete-comment-btn")) {
                if (!confirmAction("Delete this comment?")) return;
                const data = await ajax("../api/projects/delete_comment.php", "POST", { comment_id: commentId });
                if (data.status === "success") {
                    commentEl.remove();
                    updateCommentCount();
                } else alert(data.message);
            }

            // Edit comment
            if (target.classList.contains("edit-comment-btn")) {
                const p = commentEl.querySelector("p");
                const oldText = p.innerText.replace(/^.*?:\s*/, "");
                const newText = prompt("Edit your comment:", oldText);
                if (!newText || newText.trim() === oldText) return;
                const data = await ajax("../api/projects/edit_comment.php", "POST", { comment_id: commentId, comment: newText });
                if (data.status === "success") p.innerHTML = `<strong>${p.querySelector("strong").textContent}:</strong> ${newText}`;
                else alert(data.message);
            }

            // Reply to comment
            if (target.classList.contains("reply-btn")) {
                const input = commentEl.querySelector(".reply-input");
                const replyText = input.value.trim();
                if (!replyText) return;

                target.disabled = true;
                const data = await ajax("../api/projects/add_comment.php", "POST", {
                    project_id: projectId,
                    comment: replyText,
                    parent_id: commentId
                });
                target.disabled = false;

                if (data.status === "success" && data.data?.comment) {
                    const nestedDiv = commentEl.querySelector(".nested-replies") || (() => {
                        const d = document.createElement("div");
                        d.classList.add("nested-replies");
                        d.dataset.parentId = commentId;
                        commentEl.appendChild(d);
                        return d;
                    })();

                    const parentIndent = parseInt(commentEl.style.marginLeft || 0, 10);
                    const depth = parentIndent / 20 + 1;

                    nestedDiv.insertAdjacentHTML("beforeend", createCommentHTML(data.data.comment, depth));
                    fadeIn(nestedDiv.lastElementChild);
                    input.value = "";
                    updateCommentCount();
                } else alert(data.message || "Something went wrong.");
            }
        });
    }

    // -----------------------
    // Delete project
    // -----------------------
    document.getElementById("delete-project-btn")?.addEventListener("click", async () => {
        if (!confirmAction("Delete this project?")) return;
        const data = await ajax("../api/projects/delete_my_project.php", "POST", { project_id: projectId });
        if (data.status === "success") window.location.href = "index.php";
        else alert(data.message);
    });

    // -----------------------
    // Auto-refresh stats & comments
    // -----------------------
    setInterval(async () => {
        try {
            const res = await ajax(`../api/projects/get_project_stats.php?project_id=${projectId}`, "GET");

            // Correctly read data depending on structure
            const statsData = res.data ?? res; // fallback if res.data exists or not

            if (res.status === "success" && statsData) {
                const likes = statsData.like_count ?? 0;
                const downloads = statsData.download_count ?? 0;

                updateLikeCount(likes);
                updateDownloadCount(downloads);
            }
        } catch (err) {
            console.error("Auto-refresh failed:", err);
        }

        // Only reload comments if user is not typing
        const focused = document.activeElement;
        if (!focused || (!focused.closest(".reply-section") && focused !== document.querySelector("#comment"))) {
            await loadComments();
        }
    }, 5000);
});
