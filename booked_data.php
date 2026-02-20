<?php
session_start();
require_once 'db_config.php';

// 1. DATA FETCHING - BOOKINGS LIST
$query = "SELECT b.*, u.fullname, u.email, u.phone, p.package_name 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          LEFT JOIN packages p ON b.room_id = p.id 
          ORDER BY b.created_at DESC";
$res_bookings = $conn->query($query);

// 2. DATA FETCHING - MEAL FORECAST (Joined with Items)
$meal_query = "SELECT bm.*, b.check_in, b.guests, b.secret_code, u.fullname, fp.fp_name,
               GROUP_CONCAT(fpi.item_name SEPARATOR ', ') as menu_items
               FROM booking_meals bm
               JOIN bookings b ON bm.booking_id = b.id
               JOIN users u ON b.user_id = u.id
               JOIN food_packages fp ON bm.food_package_id = fp.id
               LEFT JOIN food_package_items fpi ON fp.id = fpi.food_package_id
               WHERE b.status != 'cancelled'
               GROUP BY bm.id";
$res_meals = $conn->query($meal_query);

$daily_meals = [];
$booking_events = [];

// පද්ධතියේ දත්ත සැකසීම
while($row = $res_meals->fetch_assoc()) {
    $date_obj = new DateTime($row['check_in']);
    $date_obj->modify("+".($row['day_number'] - 1)." days");
    $actual_date = $date_obj->format("Y-m-d");

    $daily_meals[$actual_date][] = [
        'booking_id' => $row['booking_id'],
        'guest' => $row['fullname'],
        'meal_type' => $row['meal_type'],
        'package' => $row['fp_name'],
        'items' => $row['menu_items'],
        'pax' => $row['guests']
    ];
}

// Calendar Events සැකසීම
foreach($daily_meals as $date => $meals) {
    $pax_count = array_sum(array_column($meals, 'pax'));
    $booking_events[] = [
        'title' => "🍴 $pax_count PAX",
        'start' => $date,
        'extendedProps' => ['type' => 'meal', 'details' => $meals]
    ];
}

// 3. BOOKING DURATION EVENTS (Check-in to Check-out)
$res_duration = $conn->query("SELECT b.id, b.check_in, b.check_out, u.fullname FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.status != 'cancelled'");
while($row = $res_duration->fetch_assoc()) {
    $booking_events[] = [
        'title' => "🏨 " . strtoupper($row['fullname']),
        'start' => $row['check_in'],
        'end' => date('Y-m-d', strtotime($row['check_out'] . ' +1 day')),
        'color' => '#c5a059',
        'textColor' => '#000',
        'extendedProps' => ['type' => 'stay']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nature Nest | Command Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Playfair+Display:ital,wght@1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Montserrat', sans-serif; background: #040604; color: white; }
        .playfair { font-family: 'Playfair Display', serif; }
        .glass { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(197, 160, 89, 0.1); backdrop-filter: blur(10px); }
        .fc-day-today { background: rgba(197, 160, 89, 0.1) !important; border: 1px solid #c5a059 !important; }
        .badge { font-size: 8px; padding: 2px 6px; font-weight: 900; border-radius: 2px; }
        .breakfast { background: #FFD700; color: #000; }
        .lunch { background: #FF8C00; color: #fff; }
        .dinner { background: #4B0082; color: #fff; }
        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.98); z-index: 1000; overflow-y: auto; padding: 40px 20px; }
        .overlay.active { display: block; }
    </style>
</head>
<body class="p-4 md:p-12">

    <div class="max-w-7xl mx-auto">
        <header class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
            <div>
                <h1 class="playfair text-5xl italic text-[#c5a059]">Sanctuary Command</h1>
                <p class="text-[10px] uppercase tracking-[0.5em] opacity-40 mt-3">Comprehensive Booking & Culinary Intelligence</p>
            </div>
            <div class="flex gap-4">
                <button onclick="window.print()" class="text-[9px] border border-white/20 px-6 py-3 uppercase tracking-widest hover:bg-white hover:text-black transition"><i class="fas fa-print mr-2"></i> Print List</button>
                <a href="admin_dashboard.php" class="text-[9px] bg-[#c5a059] text-black px-6 py-3 font-bold uppercase tracking-widest hover:bg-white transition">Dashboard</a>
            </div>
        </header>

        <div class="flex gap-1 mb-8">
            <button onclick="switchTab('list')" class="tab-btn px-8 py-4 bg-white/5 border-b-2 border-[#c5a059] text-[10px] font-bold uppercase tracking-widest">Bookings List</button>
            <button onclick="switchTab('calendar')" class="tab-btn px-8 py-4 bg-white/0 text-[10px] font-bold uppercase tracking-widest opacity-50">Operations Calendar</button>
        </div>

        <div id="tab-list" class="tab-content block">
            <div class="glass overflow-x-auto rounded-xl">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white/5 text-[10px] uppercase tracking-widest text-[#c5a059]">
                            <th class="p-6">Guest / Contact</th>
                            <th class="p-6">Stay Period</th>
                            <th class="p-6">Room / Package</th>
                            <th class="p-6">Status</th>
                            <th class="p-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-white/5">
                        <?php while($row = $res_bookings->fetch_assoc()): $isc = ($row['status'] == 'cancelled'); ?>
                            <tr class="hover:bg-white/[0.02] transition <?= $isc ? 'opacity-20' : '' ?>">
                                <td class="p-6">
                                    <span class="block font-bold text-sm"><?= $row['fullname'] ?></span>
                                    <span class="text-[10px] opacity-40"><?= $row['phone'] ?></span>
                                </td>
                                <td class="p-6">
                                    <span class="block"><?= date('M d', strtotime($row['check_in'])) ?> - <?= date('M d', strtotime($row['check_out'])) ?></span>
                                    <span class="text-[9px] opacity-40 italic">#NN-<?= $row['id'] ?></span>
                                </td>
                                <td class="p-6 italic opacity-60"><?= $row['package_name'] ?></td>
                                <td class="p-6">
                                    <span class="px-2 py-1 border <?= $isc ? 'border-red-900 text-red-500' : 'border-green-900 text-green-500' ?> text-[8px] font-bold">
                                        <?= strtoupper($row['status']) ?>
                                    </span>
                                </td>
                                <td class="p-6 text-center">
                                    <a href="thank_you.php?id=<?= $row['id'] ?>" class="hover:text-[#c5a059]"><i class="fas fa-file-invoice"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-calendar" class="tab-content hidden">
            <div class="glass p-8 rounded-xl shadow-2xl">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <div id="mealOverlay" class="overlay">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-12 border-b border-white/10 pb-6">
                <h2 id="modalDate" class="playfair text-4xl italic text-[#c5a059]">Date</h2>
                <button onclick="closeOverlay()" class="text-4xl">&times;</button>
            </div>
            <div class="glass overflow-hidden overflow-x-auto shadow-2xl">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white/5 text-[10px] uppercase tracking-widest text-[#c5a059]">
                            <th class="p-6 text-center">Meal</th>
                            <th class="p-6">Guest & Details</th>
                            <th class="p-6">Kitchen Inventory (Menu)</th>
                            <th class="p-6 text-center">PAX</th>
                        </tr>
                    </thead>
                    <tbody id="overlayBody" class="text-sm"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById('tab-' + tab).classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-b-2', 'border-[#c5a059]', 'opacity-100');
                b.classList.add('opacity-50');
            });
            event.currentTarget.classList.add('border-b-2', 'border-[#c5a059]', 'opacity-100');
            if(tab === 'calendar') renderCalendar();
        }

        let calendar;
        function renderCalendar() {
            if(!calendar) {
                calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                    initialView: 'dayGridMonth',
                    events: <?= json_encode($booking_events) ?>,
                    eventClick: function(info) {
                        if(info.event.extendedProps.type === 'meal') {
                            showMealDetails(info.event.startStr, info.event.extendedProps.details);
                        } else {
                            Swal.fire({ title: info.event.title, text: 'Active Guest Stay', background: '#0a0f0a', color: '#c5a059' });
                        }
                    }
                });
            }
            setTimeout(() => calendar.render(), 100);
        }

        function showMealDetails(date, details) {
            const body = document.getElementById('overlayBody');
            document.getElementById('modalDate').innerText = new Date(date).toDateString();
            body.innerHTML = "";
            details.forEach(m => {
                body.innerHTML += `
                    <tr class="border-b border-white/5">
                        <td class="p-6 text-center"><span class="badge ${m.meal_type.toLowerCase()}">${m.meal_type}</span></td>
                        <td class="p-6"><span class="block font-bold">${m.guest}</span><span class="text-[9px] opacity-40">Booking #NN-0${m.booking_id}</span></td>
                        <td class="p-6"><span class="block text-[#c5a059] text-xs font-bold uppercase">${m.package}</span><p class="text-[9px] opacity-60 italic">${m.items ? m.items : 'Standard Menu'}</p></td>
                        <td class="p-6 text-center text-xl font-black">${m.pax}</td>
                    </tr>`;
            });
            document.getElementById('mealOverlay').classList.add('active');
        }

        function closeOverlay() { document.getElementById('mealOverlay').classList.remove('active'); }
    </script>
</body>
</html>