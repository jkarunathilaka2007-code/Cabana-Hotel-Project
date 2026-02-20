<?php
// Session එක ආරම්භ කරන්න
session_start();

// සියලුම Session variables මකා දමන්න
$_SESSION = array();

// Session එකට අදාළ Cookie එක පද්ධතියෙන් ඉවත් කිරීම (Optional but Recommended)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// සම්පූර්ණ Session එකම විනාශ කර දමන්න
session_destroy();

// නැවත Login පේජ් එකට (හෝ Index පේජ් එකට) යොමු කරන්න
header("Location: index.php");
exit();
?>