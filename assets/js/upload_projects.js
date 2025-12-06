// ================================ 
// Upload Project JS - Sebastinian Showcase
// Advanced AJAX + Drag & Drop + Live Preview + Progress
// ================================

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("uploadForm");
    const submitBtn = form?.querySelector(".btn-submit");
    const feedback = document.getElementById("feedback");

    if (!form || !submitBtn || !feedback) return;

    // --- Drag & Drop Setup ---
    const fileInputs = form.querySelectorAll("input[type='file']");
    fileInputs.forEach(input => {
        const parent = input.parentElement;
        parent.classList.add("dropzone");

        // Drag events
        ["dragover", "dragenter"].forEach(evt => {
            parent.addEventListener(evt, e => {
                e.preventDefault();
                parent.classList.add("hover");
            });
        });

        ["dragleave", "dragend", "drop"].forEach(evt => {
            parent.addEventListener(evt, e => {
                e.preventDefault();
                parent.classList.remove("hover");
            });
        });

        parent.addEventListener("drop", e => {
            if (e.dataTransfer.files.length > 0) {
                input.files = e.dataTransfer.files;
                showPreview(input);
            }
        });

        input.addEventListener("change", () => showPreview(input));
    });

    // --- Show File / Image Preview ---
    function showPreview(input) {
        const isImage = input.id === "project_image";
        const previewId = isImage ? "imagePreview" : "filePreview";
        let container = document.getElementById(previewId);

        if (!container) {
            container = document.createElement("div");
            container.id = previewId;
            container.className = isImage ? "image-preview" : "file-preview";
            input.parentElement.appendChild(container);
        }

        container.innerHTML = "";
        const file = input.files[0];
        if (!file) return;

        if (isImage && file.type.startsWith("image/")) {
            const img = document.createElement("img");
            img.src = URL.createObjectURL(file);
            img.onload = () => URL.revokeObjectURL(img.src);
            container.appendChild(img);
        } else {
            container.textContent = `Selected file: ${file.name}`;
        }
    }

    // --- Handle Form Submission ---
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        feedback.textContent = "";
        feedback.classList.remove("success", "error");

        submitBtn.disabled = true;
        submitBtn.textContent = "Uploading...";

        const formData = new FormData(form);

        // Create/reset progress bar
        let progressBar = document.getElementById("progressBar");
        if (!progressBar) {
            progressBar = document.createElement("div");
            progressBar.id = "progressBar";
            progressBar.style.width = "0%";
            progressBar.style.height = "6px";
            progressBar.style.background = "linear-gradient(90deg, #B22222, #FFD700)";
            progressBar.style.borderRadius = "3px";
            progressBar.style.marginTop = "10px";
            progressBar.style.transition = "width 0.3s ease";
            submitBtn.parentElement.appendChild(progressBar);
        }
        progressBar.style.width = "0%";

        try {
            // Use XMLHttpRequest to properly track progress
            await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "../api/projects/upload_projects.php", true);

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percent + "%";
                    }
                };

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const result = JSON.parse(xhr.responseText);
                            if (result.status === "success") {
                                feedback.textContent = result.message || "Project uploaded successfully!";
                                feedback.classList.add("success");
                                form.reset();
                                document.querySelectorAll(".file-preview, .image-preview").forEach(el => el.remove());
                                resolve();
                            } else {
                                feedback.textContent = result.message || "An error occurred during upload.";
                                feedback.classList.add("error");
                                console.error("Server error:", result);
                                reject(result);
                            }
                        } catch (err) {
                            feedback.textContent = "Invalid server response.";
                            feedback.classList.add("error");
                            console.error("Parse error:", err, xhr.responseText);
                            reject(err);
                        }
                    } else {
                        feedback.textContent = `Server error: ${xhr.status}`;
                        feedback.classList.add("error");
                        reject(xhr.status);
                    }
                };

                xhr.onerror = function() {
                    feedback.textContent = "Upload failed due to a network error.";
                    feedback.classList.add("error");
                    reject();
                };

                xhr.send(formData);
            });
        } catch (err) {
            console.error("Upload process failed:", err);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Upload Project";
            if (progressBar) progressBar.style.width = "100%";
            setTimeout(() => { if (progressBar) progressBar.style.width = "0%"; }, 1500);
        }
    });
});
