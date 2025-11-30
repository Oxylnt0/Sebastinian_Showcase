<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Register</h2>
    <form id="registerForm" method="POST">
        <input type="text" name="full_name" placeholder="Full Name" required><br>
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>

    <div id="message"></div>

    <script>
    const form = document.getElementById('registerForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const res = await fetch('../api/auth/register.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        document.getElementById('message').innerText = data.message;
        if(data.status === 'success') form.reset();
    });
    </script>
</body>
</html>
