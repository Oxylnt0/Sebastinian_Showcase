<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/register.css">
    <style>
        /* Basic Reset */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Arial', sans-serif; background: #f5f5f5; color: #333; height: 100vh; display: flex; align-items: center; justify-content: center; }

        /* Register Card */
        .register-card {
            background: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .register-card h2 {
            margin-bottom: 20px;
            color: #007bff;
        }

        .register-card input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            transition: border 0.3s, box-shadow 0.3s;
        }
        .register-card input:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
            outline: none;
        }

        .register-card button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: #fff;
            border: none;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .register-card button:hover {
            background: #0056b3;
        }

        #message {
            margin-top: 15px;
            font-size: 14px;
            color: #dc3545; /* default error color */
        }

        .register-logo {
            margin-bottom: 20px;
        }
        .register-logo img {
            width: 80px;
            height: 80px;
        }

        p.link {
            margin-top: 15px;
            font-size: 14px;
        }

        p.link a {
            color: #007bff;
            text-decoration: none;
        }
        p.link a:hover { text-decoration: underline; }

        /* Responsive */
        @media(max-width: 480px) {
            .register-card { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="register-logo">
        <img src="../assets/img/logo.png" alt="Sebastinian Showcase Logo">
    </div>
    <h2>Create Account</h2>
    <form id="registerForm" method="POST">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>

    <div id="message"></div>

    <p class="link">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

<script>
const form = document.getElementById('registerForm');
const messageDiv = document.getElementById('message');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    messageDiv.style.color = '#007bff';
    messageDiv.textContent = 'Registering...';

    const formData = new FormData(form);
    try {
        const res = await fetch('../api/auth/register.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if(data.status === 'success') {
            messageDiv.style.color = '#28a745';
            messageDiv.textContent = data.message;
            form.reset();
            setTimeout(() => { window.location.href = 'login.php'; }, 1000);
        } else {
            messageDiv.style.color = '#dc3545';
            messageDiv.textContent = data.message;
        }
    } catch (err) {
        messageDiv.style.color = '#dc3545';
        messageDiv.textContent = 'An error occurred. Please try again.';
        console.error(err);
    }
});
</script>

</body>
</html>
