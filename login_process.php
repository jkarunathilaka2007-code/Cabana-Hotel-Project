<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 1. ADMIN පරීක්ෂාව
    $admin_query = "SELECT * FROM admins WHERE email = '$email' LIMIT 1";
    $admin_res = $conn->query($admin_query);

    if ($admin_res->num_rows > 0) {
        $admin = $admin_res->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php?login=success");
            exit();
        }
    }

    // 2. USER පරීක්ෂාව
    $user_query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $user_res = $conn->query($user_query);

    if ($user_res->num_rows > 0) {
        $user = $user_res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['role'] = 'user';
            // මෙන්න මෙතන තමයි වෙනස් කළේ:
            header("Location: index.php?login=success");
            exit();
        }
    }

    // 3. වැරදි නම් ආපහු Login එකට
    header("Location: login.php?error=invalid_credentials");
    exit();
}