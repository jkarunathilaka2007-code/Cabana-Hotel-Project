<?php
session_start();
require_once 'db_config.php';

// Image Upload පහසු කරවන Function එක
function upload($file_array, $index, $folder = "uploads/menu/") {
    if (!isset($file_array['name'][$index]) || $file_array['error'][$index] !== 0) {
        return null;
    }
    if (!file_exists($folder)) { mkdir($folder, 0777, true); }
    
    $file_ext = pathinfo($file_array['name'][$index], PATHINFO_EXTENSION);
    $file_name = uniqid() . "." . $file_ext;
    $target_file = $folder . $file_name;
    
    return move_uploaded_file($file_array["tmp_name"][$index], $target_file) ? $target_file : null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. Main Food Package එක සේව් කිරීම ---
    $fp_name = mysqli_real_escape_string($conn, $_POST['fp_name']);
    $fp_lkr = $_POST['fp_price_lkr'];
    $fp_usd = $_POST['fp_price_usd'];
    
    // Cover Image Upload
    $fp_cover = null;
    if (isset($_FILES['fp_cover']) && $_FILES['fp_cover']['error'] == 0) {
        $fp_cover = "uploads/menu/cover_" . time() . "_" . $_FILES['fp_cover']['name'];
        move_uploaded_file($_FILES['fp_cover']['tmp_name'], $fp_cover);
    }

    $stmt = $conn->prepare("INSERT INTO food_packages (fp_name, price_lkr, price_usd, cover_image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdds", $fp_name, $fp_lkr, $fp_usd, $fp_cover);
    $stmt->execute();
    $last_fp_id = $conn->insert_id; // අලුතින් හැදුණු Package ID එක

    // --- 2. Sub-Items (Foods, Drinks, Free) සේව් කිරීම ---
    $sections = [
        'food' => ['names' => 'food_names', 'imgs' => 'food_images'],
        'drink' => ['names' => 'drink_names', 'imgs' => 'drink_images'],
        'free' => ['names' => 'free_names', 'imgs' => 'free_images']
    ];

    foreach ($sections as $type => $keys) {
        if (isset($_POST[$keys['names']])) {
            foreach ($_POST[$keys['names']] as $index => $item_name) {
                if (empty($item_name)) continue;

                // ඒ ඒ අයිටම් එකට අදාළ Image එක Upload කිරීම
                $uploaded_item_img = upload($_FILES[$keys['imgs']], $index);

                $stmt_item = $conn->prepare("INSERT INTO food_package_items (food_package_id, item_type, item_name, item_image) VALUES (?, ?, ?, ?)");
                $stmt_item->bind_param("isss", $last_fp_id, $type, $item_name, $uploaded_item_img);
                $stmt_item->execute();
            }
        }
    }

    // සාර්ථක නම් Dashboard එකට යවන්න
    header("Location: admin_dashboard.php?msg=FoodPackageCreated");
    exit();
}
?>