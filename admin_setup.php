<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin Setup | Nature Nest</title>
    <style>
        body { background: #0f1a0f; color: white; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .setup-card { background: rgba(255,255,255,0.05); border: 1px solid #c5a059; padding: 40px; width: 400px; }
        input { background: transparent; border: 1px solid rgba(255,255,255,0.2); width: 100%; padding: 10px; margin: 10px 0; color: white; outline: none; }
        input:focus { border-color: #c5a059; }
        button { background: #c5a059; width: 100%; padding: 12px; font-weight: bold; margin-top: 10px; transition: 0.3s; }
        button:hover { background: white; color: black; }
    </style>
</head>
<body>

<div class="setup-card">
    <h2 style="color: #c5a059; font-size: 20px; text-align: center; margin-bottom: 20px;">ADMIN INITIAL SETUP</h2>
    
    <?php
    require_once 'db_config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO admins (username, email, password) VALUES ('$username', '$email', '$password')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: #4ade80; text-align: center; margin-bottom: 10px;'>Admin account created successfully!</p>";
        } else {
            echo "<p style='color: #f87171; text-align: center; margin-bottom: 10px;'>Error: " . $conn->error . "</p>";
        }
    }
    ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Admin Username" required>
        <input type="email" name="email" placeholder="Admin Email" required>
        <input type="password" name="password" placeholder="Admin Password" required>
        <button type="submit">CREATE ADMIN</button>
    </form>
    
    <p style="font-size: 10px; opacity: 0.5; text-align: center; margin-top: 20px;">Delete this file after creating the first admin for security.</p>
</div>

</body>
</html>