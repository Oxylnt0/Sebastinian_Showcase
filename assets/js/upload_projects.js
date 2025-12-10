document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================
    // 1. GOD MODE: HTML5 CANVAS PARTICLE ENGINE
    // =========================================================
    const initParticles = () => {
        const canvas = document.getElementById('luxury-canvas');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        
        const particleCount = 60; 
        const connectionDistance = 150; 
        const mouseDistance = 200; 

        let mouse = { x: null, y: null };

        window.addEventListener('mousemove', (e) => {
            mouse.x = e.x;
            mouse.y = e.y;
        });

        const resize = () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        };
        
        window.addEventListener('resize', resize);
        resize();

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.5; 
                this.vy = (Math.random() - 0.5) * 0.5; 
                this.size = Math.random() * 2 + 1;
                this.color = `rgba(212, 175, 55, ${Math.random() * 0.5 + 0.1})`; 
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                if (this.x < 0 || this.x > width) this.vx *= -1;
                if (this.y < 0 || this.y > height) this.vy *= -1;

                let dx = mouse.x - this.x;
                let dy = mouse.y - this.y;
                let distance = Math.sqrt(dx*dx + dy*dy);

                if (distance < mouseDistance) {
                    const forceDirectionX = dx / distance;
                    const forceDirectionY = dy / distance;
                    const force = (mouseDistance - distance) / mouseDistance;
                    const directionX = forceDirectionX * force * 2; 
                    const directionY = forceDirectionY * force * 2;
                    this.x -= directionX;
                    this.y -= directionY;
                }
            }

            draw() {
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }

        const animate = () => {
            ctx.clearRect(0, 0, width, height);
            
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();

                for (let j = i; j < particles.length; j++) {
                    let dx = particles[i].x - particles[j].x;
                    let dy = particles[i].y - particles[j].y;
                    let distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < connectionDistance) {
                        ctx.beginPath();
                        ctx.strokeStyle = `rgba(128, 0, 0, ${1 - distance/connectionDistance})`;
                        ctx.lineWidth = 0.5;
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(animate);
        };
        animate();
    };

    initParticles();


    // =========================================================
    // 2. ADVANCED DROPZONE (Simulated Scanning)
    // =========================================================
    
    const simulateScan = (zone, file, previewCallback) => {
        const overlay = document.createElement('div');
        overlay.className = 'scanning-overlay';
        overlay.innerHTML = `
            <div class="scan-text">SCANNING ASSETS...</div>
            <div class="scanning-bar"></div>
        `;
        zone.appendChild(overlay);

        const bar = overlay.querySelector('.scanning-bar');
        let width = 0;

        const interval = setInterval(() => {
            width += Math.random() * 15;
            if (width > 100) width = 100;
            bar.style.width = width + '%';

            if (width === 100) {
                clearInterval(interval);
                setTimeout(() => {
                    overlay.remove(); 
                    previewCallback(); 
                }, 300);
            }
        }, 100);
    };

    const setupDropzone = (zoneId, inputId, previewId, isImage = false) => {
        const zone = document.getElementById(zoneId);
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);

        if (!zone || !input || !preview) return;

        ['dragenter', 'dragover'].forEach(eventName => {
            zone.addEventListener(eventName, (e) => {
                e.preventDefault(); e.stopPropagation();
                zone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            zone.addEventListener(eventName, (e) => {
                e.preventDefault(); e.stopPropagation();
                zone.classList.remove('dragover');
            }, false);
        });

        const handleFile = (file) => {
            if (!file) return;
            
            if (isImage && !file.type.startsWith('image/')) {
                alert('Please upload an image file.');
                input.value = ''; // Reset input
                return;
            }

            zone.classList.add('has-file');

            simulateScan(zone, file, () => {
                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    let iconClass = 'fa-file-alt';
                    if (file.name.includes('.pdf')) iconClass = 'fa-file-pdf';
                    preview.innerHTML = `
                        <div class="file-info-box">
                            <i class="far ${iconClass} file-icon-lg"></i>
                            <span class="file-name">${file.name}</span>
                        </div>`;
                }
            });
        };

        input.addEventListener('change', (e) => handleFile(e.target.files[0]));
        zone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            input.files = files;
            handleFile(files[0]);
        });
    };

    setupDropzone('fileZone', 'project_file', 'filePreview', false);
    setupDropzone('imageZone', 'project_image', 'imagePreview', true);


    // =========================================================
    // 3. SUBMISSION LOGIC (Double-Click Fixed)
    // =========================================================
    
    const form = document.getElementById('uploadForm');
    const modal = document.getElementById('uploadModal');
    const loadingState = document.querySelector('.loading-state');
    const successState = document.querySelector('.success-state');
    const submitBtn = document.getElementById('submitBtn');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // --- FIX: Prevent Double Submission ---
            if (submitBtn.disabled) return; 
            submitBtn.disabled = true; // Lock the button immediately

            // Validation
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const fileInput = document.getElementById('project_file');

            if (!title || !description || !fileInput.files.length) {
                shakeButton();
                submitBtn.disabled = false; // Unlock if invalid
                return;
            }

            // Show Loading Modal
            modal.classList.add('active');
            loadingState.style.display = 'block';
            successState.style.display = 'none';

            const formData = new FormData(form);

            try {
                const response = await fetch('../api/projects/upload_projects.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    setTimeout(() => {
                        loadingState.style.display = 'none';
                        successState.style.display = 'block';
                        triggerConfetti();
                        // Note: We DO NOT re-enable the button here to prevent duplicates
                    }, 1000);
                } else {
                    closeModal();
                    showStatus(result.message, 'error');
                    submitBtn.disabled = false; // Unlock on server error
                }
            } catch (error) {
                console.error(error);
                closeModal();
                showStatus('Network error occurred.', 'error');
                submitBtn.disabled = false; // Unlock on network error
            }
        });
    }

    // =========================================================
    // 4. HELPER FUNCTIONS
    // =========================================================

    function triggerConfetti() {
        if (typeof confetti === 'function') {
            var duration = 3 * 1000;
            var animationEnd = Date.now() + duration;
            var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 2000 };
            var random = (min, max) => Math.random() * (max - min) + min;

            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();
                if (timeLeft <= 0) return clearInterval(interval);
                var particleCount = 50 * (timeLeft / duration);
                
                confetti(Object.assign({}, defaults, { 
                    particleCount, 
                    origin: { x: random(0.1, 0.3), y: Math.random() - 0.2 },
                    colors: ['#D4AF37', '#800000'] 
                }));
                confetti(Object.assign({}, defaults, { 
                    particleCount, 
                    origin: { x: random(0.7, 0.9), y: Math.random() - 0.2 },
                    colors: ['#D4AF37', '#800000']
                }));
            }, 250);
        }
    }

    function closeModal() {
        modal.classList.remove('active');
    }

    function showStatus(msg, type) {
        const statusBox = document.getElementById('statusMessage');
        if(statusBox) {
            statusBox.textContent = msg;
            statusBox.className = `status-message ${type}`;
            statusBox.style.display = 'block';
            setTimeout(() => { statusBox.style.display = 'none'; }, 4000);
        } else {
            alert(msg);
        }
    }

    function shakeButton() {
        submitBtn.style.animation = 'none';
        submitBtn.offsetHeight; /* trigger reflow */
        submitBtn.style.animation = 'shake 0.5s';
        setTimeout(() => { submitBtn.style.animation = ''; }, 500);
    }

    // Add Shake/Status CSS dynamically
    const styleSheet = document.createElement("style");
    styleSheet.innerText = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .status-message { display: none; margin-bottom: 15px; padding: 10px; border-radius: 8px; font-weight: 600; text-align: center; }
        .status-message.error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
    `;
    document.head.appendChild(styleSheet);

    // Init 3D Tilt
    if (typeof VanillaTilt !== "undefined") {
        VanillaTilt.init(document.querySelectorAll("[data-tilt]"), {
            max: 5, speed: 400, glare: true, "max-glare": 0.2
        });
    }
});