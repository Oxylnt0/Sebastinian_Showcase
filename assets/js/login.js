document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const loginButton = document.getElementById('btn-login');
    const messageBox = document.createElement('div');
    messageBox.id = 'login-message';
    messageBox.style.marginBottom = '1rem';
    loginForm.prepend(messageBox);

    // Function to display messages
    function showMessage(message, type = 'error') {
        messageBox.textContent = message;
        messageBox.style.color = type === 'success' ? '#FFD700' : '#B71C1C';
        messageBox.style.fontWeight = 'bold';
        messageBox.style.textAlign = 'center';
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Disable button during login
        loginButton.disabled = true;
        loginButton.textContent = 'Logging in...';

        const formData = new FormData(loginForm);

        try {
            const res = await fetch('../api/auth/login.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.status === 'success') {
                showMessage(data.message, 'success');

                // Redirect after short delay
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1000);

            } else {
                showMessage(data.message, 'error');
            }

        } catch (err) {
            console.error(err);
            showMessage('An unexpected error occurred.', 'error');
        } finally {
            loginButton.disabled = false;
            loginButton.textContent = 'Login';
        }
    });
});
