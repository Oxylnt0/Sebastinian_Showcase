<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Login</h2>
    <form id="loginForm" method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>

    <div id="message"></div>

    <script>
    const form = document.getElementById('loginForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const res = await fetch('../api/auth/login.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        document.getElementById('message').innerText = data.message;
        if(data.status === 'success') {
            window.location.href = 'dashboard.php';
        }
    });
    </script>
</body>
</html>
