<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Form එකෙන් එන දත්ත ලබාගැනීම
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $max_guests = (int)$_POST['max_guests'];
    $price_lkr = (float)$_POST['price_lkr'];
    $price_usd = (float)$_POST['price_usd'];
    
    // Amenities ටික Array එකක් විදියට අරගෙන JSON බවට පත් කිරීම
    // හිස් ඒව අයින් කරනවා (array_filter)
    $amenities_array = array_filter($_POST['amenities']);
    $amenities_json = json_encode(array_values($amenities_array));

    // Image Upload Logic
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_path = "";
    if (isset($_FILES['p_image']) && $_FILES['p_image']['error'] == 0) {
        $file_name = time() . "_" . basename($_FILES["p_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["p_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Database එකට ඇතුළත් කිරීම
    $sql = "INSERT INTO packages (package_name, max_guests, price_lkr, price_usd, cover_image, amenities) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siddss", $p_name, $max_guests, $price_lkr, $price_usd, $image_path, $amenities_json);

    if ($stmt->execute()) {
        // සාර්ථක නම් ඊළඟ පියවර වන Food Menu එකට යවනවා
        $_SESSION['last_package_id'] = $conn->insert_id;
        header("Location: admin_dashboard.php?status=success");
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>