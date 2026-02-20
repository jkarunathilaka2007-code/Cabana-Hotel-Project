<?php
session_start();
require_once 'db_config.php';

// 1. යූසර් ලොගින් වෙලාද කියලා බලමු
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'කරුණාකර පළමුව ලොගින් වන්න.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'දත්ත ලැබුණේ නැත.']);
    exit;
}

try {
    // Transaction එකක් ආරම්භ කිරීම (එක තැනක වැරදුණොත් මුළු බුකින් එකම අවලංගු කිරීමට)
    $conn->begin_transaction();

    // 2. Secret Code එකක් ජනනය කිරීම (Reception එකේදී පෙන්වීමට)
    $secret_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    // 3. ප්‍රධාන Bookings ටේබල් එකට දත්ත දැමීම (user_id සහ status ද ඇතුළුව)
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, secret_code, check_in, check_out, nights, residency, guests, room_id, total_price, currency, transport_needed, pickup_loc, pickup_time, drop_loc, drop_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')");
    
    $transport_needed = $data['transport'] ? 1 : 0;
    $pickup_loc = $data['transport']['pickup_loc'] ?? null;
    $pickup_time = $data['transport']['pickup_time'] ?? null;
    $drop_loc = $data['transport']['drop_loc'] ?? null;
    $drop_time = $data['transport']['drop_time'] ?? null;

    $stmt->bind_param("isssisiidsissss", 
        $user_id,
        $secret_code,
        $data['check_in'], 
        $data['check_out'], 
        $data['nights'], 
        $data['residency'], 
        $data['guests'], 
        $data['room_id'], 
        $data['total_price'], 
        $data['currency'],
        $transport_needed,
        $pickup_loc,
        $pickup_time,
        $drop_loc,
        $drop_time
    );
    $stmt->execute();
    $booking_id = $conn->insert_id;

    // 4. Booking Meals ටේබල් එකට දත්ත දැමීම
    if (!empty($data['selections'])) {
        $meal_stmt = $conn->prepare("INSERT INTO booking_meals (booking_id, day_number, meal_type, food_package_id, price_at_booking) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($data['selections'] as $day_key => $meals) {
            $day_num = preg_replace('/[^0-9]/', '', $day_key);
            foreach ($meals as $type => $meal) {
                if ($meal && isset($meal['id'])) {
                    $meal_stmt->bind_param("iissi", 
                        $booking_id, 
                        $day_num, 
                        $type, 
                        $meal['id'], 
                        $meal['price']
                    );
                    $meal_stmt->execute();
                }
            }
        }
    }

    // 5. Booking Activities ටේබල් එකට දත්ත දැමීම
    if (!empty($data['activities'])) {
        $act_stmt = $conn->prepare("INSERT INTO booking_activities (booking_id, activity_id, price_at_booking) VALUES (?, ?, ?)");
        foreach ($data['activities'] as $act) {
            $act_stmt->bind_param("iii", $booking_id, $act['id'], $act['price']);
            $act_stmt->execute();
        }
    }

    // සියල්ල හරි නම් දත්ත ස්ථිර කරන්න
    $conn->commit();

    echo json_encode([
        'success' => true, 
        'booking_id' => $booking_id,
        'secret_code' => $secret_code
    ]);

} catch (Exception $e) {
    // දෝෂයක් ආවොත් සේව් වුණු කොටසත් අයින් කරන්න
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]);
}
?>