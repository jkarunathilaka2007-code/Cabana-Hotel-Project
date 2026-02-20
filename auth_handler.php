<?php
session_start();
require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Cormorant+Garamond:ital,wght@1,600&display=swap" rel="stylesheet">
    <style>
        body { 
            margin: 0;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('img/hero.jpg'); 
            background-size: cover; background-position: center; height: 100vh;
            font-family: 'Montserrat', sans-serif;
        }
        .swal2-popup { 
            background: rgba(26, 46, 26, 0.95) !important; 
            backdrop-filter: blur(10px);
            color: white !important; 
            border-radius: 0px !important; 
            border: 1px solid #c5a059 !important;
        }
        .swal2-title { color: #c5a059 !important; text-transform: uppercase; letter-spacing: 3px; font-family: 'Cormorant Garamond', serif; font-style: italic; }
        .swal2-html-container { color: rgba(255,255,255,0.8) !important; font-size: 0.85rem !important; }
        .swal2-styled.swal2-confirm { background-color: #c5a059 !important; border-radius: 0 !important; text-transform: uppercase !important; font-size: 11px !important; letter-spacing: 2px !important; padding: 12px 30px !important; border: none !important; }
    </style>
</head>
<body>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    // --- 1. REGISTRATION LOGIC (For Users) ---
    if ($action == 'register') {
        $fullname = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $nationality = mysqli_real_escape_string($conn, $_POST['nationality']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Check if user already exists
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            echo "<script>
                Swal.fire({ icon: 'error', title: 'Reservation Denied', text: 'This email is already part of our sanctuary.' })
                .then(() => { window.history.back(); });
            </script>";
        } else {
            $sql = "INSERT INTO users (fullname, email, phone, nationality, password) 
                    VALUES ('$fullname', '$email', '$phone', '$nationality', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "<script>
                    Swal.fire({ icon: 'success', title: 'The Nest Awaits', text: 'Welcome $fullname. Your journey begins here.', confirmButtonText: 'Proceed to Login' })
                    .then(() => { window.location.href = 'login.php'; });
                </script>";
            }
        }
    }

    // --- 2. SMART LOGIN LOGIC (Admins & Users) ---
    if ($action == 'login') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];

        // STEP A: Check if it's an ADMIN
        $admin_res = $conn->query("SELECT * FROM admins WHERE email = '$email'");
        if ($admin_res->num_rows > 0) {
            $admin = $admin_res->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];
                $_SESSION['role'] = 'admin';

                echo "<script>
                    Swal.fire({ icon: 'success', title: 'Admin Access', text: 'Welcome back, Manager.', showConfirmButton: false, timer: 1800 })
                    .then(() => { window.location.href = 'admin_dashboard.php'; });
                </script>";
                exit();
            }
        }

        // STEP B: If not admin, check if it's a USER
        $user_res = $conn->query("SELECT * FROM users WHERE email = '$email'");
        if ($user_res->num_rows > 0) {
            $user = $user_res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['role'] = 'user';

                echo "<script>
                    Swal.fire({ icon: 'success', title: 'Namaste', text: 'Returning to your peaceful escape...', showConfirmButton: false, timer: 1800 })
                    .then(() => { window.location.href = 'index.php'; });
                </script>";
                exit();
            }
        }

        // STEP C: No match found
        echo "<script>
            Swal.fire({ icon: 'error', title: 'Access Denied', text: 'Invalid email or password.' })
            .then(() => { window.history.back(); });
        </script>";
    }
}

// --- 3. LOGOUT LOGIC ---
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

</body>
</html>