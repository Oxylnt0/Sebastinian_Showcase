document.addEventListener("DOMContentLoaded", () => {

    // Elements
    const form = document.getElementById("project-form");
    const titleInput = document.getElementById("title");
    const descriptionInput = document.getElementById("description");
    const sdgSelect = document.getElementById("sdg_id");
    const imageInput = document.getElementById("image");
    const fileInput = document.getElementById("file");
    const submitBtn = document.getElementById("btn-submit");

    // Load SDGs dynamically
    async function loadSDGs() {
        try {
            const res = await fetch("../api/projects/get_projects.php");
            const data = await res.json();
            if (data.status === "success") {
                const sdgSet = new Set();
                data.data.forEach(p => {
                    if (p.sdg.sdg_id) sdgSet.add(JSON.stringify(p.sdg));
                });

                sdgSet.forEach(sdgStr => {
                    const sdg = JSON.parse(sdgStr);
                    const option = document.createElement("option");
                    option.value = sdg.sdg_id;
                    option.textContent = sdg.sdg_name;
                    sdgSelect.appendChild(option);
                });
            }
        } catch (err) {
            console.error("Failed to load SDGs:", err);
        }
    }

    loadSDGs();

    // Form Submission
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // Basic client-side validation
        if (!titleInput.value.trim()) {
            alert("Project title is required.");
            return;
        }
        if (!descriptionInput.value.trim()) {
            alert("Project description is required.");
            return;
        }
        if (!sdgSelect.value) {
            alert("Please select a Sustainable Development Goal.");
            return;
        }

        // Disable submit button to prevent multiple clicks
        submitBtn.disabled = true;
        submitBtn.textContent = "Submitting...";

        const formData = new FormData(form);

        try {
            const res = await fetch("../api/projects/upload_project.php", {
                method: "POST",
                body: formData
            });
            const data = await res.json();

            if (data.status === "success") {
                alert(data.message);
                form.reset();
            } else {
                alert(`Error: ${data.message}`);
            }

        } catch (err) {
            console.error("Submission failed:", err);
            alert("Failed to submit the project. Please try again.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Submit Project";
        }
    });

    // Optional: Show selected file names
    imageInput.addEventListener("change", () => {
        if (imageInput.files.length > 0) {
            const label = document.querySelector('label[for="image"]');
            label.textContent = `Selected Image: ${imageInput.files[0].name}`;
        }
    });

    fileInput.addEventListener("change", () => {
        if (fileInput.files.length > 0) {
            const label = document.querySelector('label[for="file"]');
            label.textContent = `Selected File: ${fileInput.files[0].name}`;
        }
    });

});
