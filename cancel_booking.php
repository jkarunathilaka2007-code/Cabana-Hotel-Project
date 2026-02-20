<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: booked_detail.php");
    exit();
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// යූසර්ට අයිති බුකින් එකක්මද කියලා සහ දැනටමත් cancelled ද කියලා බලනවා
$stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status != 'cancelled'");
$stmt->bind_param("ii", $booking_id, $user_id);

if ($stmt->execute()) {
    // සාර්ථකව cancel වුණාම ආපහු ලිස්ට් එකට යවනවා
    header("Location: booked_detail.php?msg=cancelled");
} else {
    header("Location: booked_detail.php?msg=error");
}
exit();