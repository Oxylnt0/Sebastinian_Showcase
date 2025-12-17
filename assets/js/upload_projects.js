document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================
    // 1. PARTICLES (Visuals)
    // =========================================================
    // ... (Keep your existing particle code here, it was fine) ...
    // I will omit the particle code block for brevity, 
    // simply Paste your existing initParticles() function here.
    const initParticles = () => { /* ...paste your particle code... */ };
    initParticles();


    // =========================================================
    // 2. PDF HANDLING & THUMBNAIL GENERATION
    // =========================================================
    
    const setupDropzone = () => {
        const zone = document.getElementById('fileZone');
        const input = document.getElementById('project_file');
        const preview = document.getElementById('filePreview');
        const thumbCard = document.getElementById('thumbCard');
        const hiddenThumbInput = document.getElementById('generated_thumbnail');

        if (!zone || !input) return;

        // Drag Effects
        ['dragenter', 'dragover'].forEach(evt => {
            zone.addEventListener(evt, (e) => {
                e.preventDefault(); zone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            zone.addEventListener(evt, (e) => {
                e.preventDefault(); zone.classList.remove('dragover');
            });
        });

        // Main File Handler
        const handleFile = async (file) => {
            if (!file) return;

            if (file.type !== 'application/pdf') {
                alert('Please upload a PDF file only.');
                input.value = '';
                return;
            }

            // Visual Update
            zone.classList.add('has-file');
            preview.innerHTML = `
                <div class="file-info-box">
                    <i class="fas fa-file-pdf file-icon-lg"></i>
                    <span class="file-name">${file.name}</span>
                </div>`;

            // --- START PDF GENERATION ---
            try {
                // Show "Generating" text in sidebar
                thumbCard.style.display = 'block';
                thumbCard.querySelector('#pdf-canvas-container').innerHTML = '<p style="padding:20px; color:#gold;">Scanning Page 1...</p>';

                const arrayBuffer = await file.arrayBuffer();
                const pdf = await pdfjsLib.getDocument(arrayBuffer).promise;
                const page = await pdf.getPage(1); // Get Page 1

                const viewport = page.getViewport({ scale: 0.5 }); // Scale down for thumbnail
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                await page.render({ canvasContext: context, viewport: viewport }).promise;

                // 1. Display in Sidebar
                const container = document.getElementById('pdf-canvas-container');
                container.innerHTML = ''; // Clear loading text
                canvas.style.width = "100%"; // CSS fit
                canvas.style.height = "auto";
                container.appendChild(canvas);

                // 2. Save Base64 to Hidden Input
                const dataURL = canvas.toDataURL('image/jpeg', 0.8);
                hiddenThumbInput.value = dataURL; 

            } catch (error) {
                console.error("PDF Gen Error:", error);
                thumbCard.style.display = 'none'; // Hide if failed
            }
        };

        input.addEventListener('change', (e) => handleFile(e.target.files[0]));
        zone.addEventListener('drop', (e) => {
            input.files = e.dataTransfer.files;
            handleFile(e.dataTransfer.files[0]);
        });
    };

    setupDropzone(); // Call the function


    // =========================================================
    // 3. SUBMISSION LOGIC
    // =========================================================
    
    const form = document.getElementById('uploadForm');
    const modal = document.getElementById('uploadModal');
    const submitBtn = document.getElementById('submitBtn');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (submitBtn.disabled) return; 
            submitBtn.disabled = true; 

            // ... inside form.addEventListener ...

            // Validation
            const title = document.getElementById('title').value.trim();
            const authors = document.getElementById('authors').value.trim();
            const date = document.getElementById('publication_date').value;
            const type = document.getElementById('research_type').value; 
            const dept = document.getElementById('department').value;    
            const description = document.getElementById('description').value.trim(); // Make sure this line exists
            const fileInput = document.getElementById('project_file');

            // Check if any field is empty
            // MAKE SURE !description IS IN THIS LIST:
            if (!title || !authors || !date || !type || !dept || !description || !fileInput.files.length) {
                alert("Please fill all fields, including the Abstract.");
                submitBtn.disabled = false;
                return;
            }

            // Show Loading
            modal.classList.add('active');
            document.querySelector('.loading-state').style.display = 'block';
            document.querySelector('.success-state').style.display = 'none';

            const formData = new FormData(form);

            try {
                const response = await fetch('../api/projects/upload_projects.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    setTimeout(() => {
                        document.querySelector('.loading-state').style.display = 'none';
                        document.querySelector('.success-state').style.display = 'block';
                        triggerConfetti();
                    }, 1000);
                } else {
                    modal.classList.remove('active');
                    alert(result.message);
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                modal.classList.remove('active');
                alert("Network error.");
                submitBtn.disabled = false;
            }
        });
    }

    // Confetti Helper
    function triggerConfetti() {
        var duration = 3 * 1000;
        var animationEnd = Date.now() + duration;
        var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 2000 };
        var random = (min, max) => Math.random() * (max - min) + min;

        var interval = setInterval(function() {
            var timeLeft = animationEnd - Date.now();
            if (timeLeft <= 0) return clearInterval(interval);
            var particleCount = 50 * (timeLeft / duration);
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: random(0.1, 0.3), y: Math.random() - 0.2 }, colors: ['#D4AF37', '#800000'] }));
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: random(0.7, 0.9), y: Math.random() - 0.2 }, colors: ['#D4AF37', '#800000'] }));
        }, 250);
    }
    
    // Init Tilt
    if (typeof VanillaTilt !== "undefined") {
        VanillaTilt.init(document.querySelectorAll("[data-tilt]"), { max: 5, speed: 400, glare: true, "max-glare": 0.2 });
    }
});