<?php
session_start();
require_once 'db_config.php';

// Booking ID එක නැත්නම් මුල් පිටුවට යවන්න
if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$booking_id = $_GET['id'];

// 1. ප්‍රධාන විස්තර ලබා ගැනීම
$query = "SELECT b.*, p.package_name FROM bookings b LEFT JOIN packages p ON b.room_id = p.id WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) { echo "Invalid Booking ID"; exit(); }

// 2. කෑම වේල් සියල්ල ලබා ගැනීම
$meals_query = "SELECT bm.*, fp.fp_name FROM booking_meals bm JOIN food_packages fp ON bm.food_package_id = fp.id WHERE bm.booking_id = ? ORDER BY bm.day_number ASC";
$m_stmt = $conn->prepare($meals_query);
$m_stmt->bind_param("i", $booking_id);
$m_stmt->execute();
$meals_res = $m_stmt->get_result();
$all_meals = [];
while($m = $meals_res->fetch_assoc()) { $all_meals[] = $m; }

// 3. ඇක්ටිවිටීස් ලබා ගැනීම
$acts_query = "SELECT ba.*, a.activity_name FROM booking_activities ba JOIN activities a ON ba.activity_id = a.id WHERE ba.booking_id = ?";
$a_stmt = $conn->prepare($acts_query);
$a_stmt->bind_param("i", $booking_id);
$a_stmt->execute();
$acts_res = $a_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nature Nest | Your Digital Receipt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Playfair+Display:ital,wght@1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        body { font-family: 'Montserrat', sans-serif; background: #050805; color: white; }
        .playfair { font-family: 'Playfair Display', serif; }
        .invoice-card { background: #0a0f0a; border: 1px solid rgba(197, 160, 89, 0.2); }
        .no-print { user-select: none; }
    </style>
</head>
<body class="p-4 md:p-10 flex flex-col items-center">

    <div class="max-w-4xl w-full flex justify-between items-center mb-8 no-print">
        <a href="index.php" class="text-[10px] uppercase tracking-widest opacity-50 hover:opacity-100 transition">← Back to Sanctuary</a>
        <button onclick="downloadAsImage()" class="bg-[#c5a059] text-black px-8 py-3 text-[10px] font-bold uppercase tracking-widest hover:bg-white transition shadow-2xl">
            <i class="fas fa-camera mr-2"></i> Save to Gallery (PNG)
        </button>
    </div>

    <div id="capture-area" class="max-w-4xl w-full invoice-card p-8 md:p-16 rounded-sm shadow-2xl overflow-hidden">
        
        <div class="flex justify-between items-start border-b border-white/10 pb-10">
            <div>
                <h1 class="playfair text-4xl italic text-[#c5a059]">Nature Nest</h1>
                <p class="text-[8px] uppercase tracking-[0.5em] opacity-40">Bespoke Eco-Stay</p>
            </div>
            <div class="text-right">
                <p class="text-[9px] uppercase tracking-widest opacity-40 mb-1">Reception Secret Code</p>
                <div class="bg-[#c5a059]/10 px-4 py-2 border border-[#c5a059]/30">
                    <p class="text-3xl font-bold text-[#c5a059] tracking-[0.2em]"><?= $booking['secret_code'] ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 py-10 border-b border-white/10 text-[10px] uppercase tracking-widest">
            <div>
                <p class="opacity-30 mb-1">Booking Ref</p>
                <p class="font-bold">#NN-00<?= $booking['id'] ?></p>
            </div>
            <div>
                <p class="opacity-30 mb-1">Guests</p>
                <p class="font-bold"><?= $booking['guests'] ?> Persons</p>
            </div>
            <div>
                <p class="opacity-30 mb-1">Stay</p>
                <p class="font-bold"><?= $booking['nights'] ?> Nights</p>
            </div>
            <div class="text-right">
                <p class="opacity-30 mb-1">Check-In</p>
                <p class="font-bold"><?= date('d M Y', strtotime($booking['check_in'])) ?></p>
            </div>
        </div>

        <div class="py-10">
            <h3 class="playfair text-xl italic text-[#c5a059] mb-8">Reservation Details</h3>
            
            <table class="w-full text-left text-[11px] uppercase tracking-widest">
                <thead class="opacity-40 border-b border-white/5 text-[9px]">
                    <tr>
                        <th class="py-4 font-normal">Description</th>
                        <th class="py-4 font-normal text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr>
                        <td class="py-6">
                            <span class="text-lg playfair normal-case italic"><?= $booking['package_name'] ?> Suite</span>
                        </td>
                        <td class="py-6 text-right opacity-60 italic">Confirmed</td>
                    </tr>

                    <?php 
                    $curr_day = 0;
                    foreach($all_meals as $m): 
                        if($curr_day != $m['day_number']): $curr_day = $m['day_number'];
                    ?>
                        <tr class="bg-white/5"><td colspan="2" class="py-2 pl-3 text-[9px] italic text-[#c5a059]">Day <?= $curr_day ?> Dining Plan</td></tr>
                    <?php endif; ?>
                    <tr>
                        <td class="py-3 pl-6 opacity-60 lowercase italic"><?= str_replace('_',' ',$m['meal_type']) ?>: <?= $m['fp_name'] ?></td>
                        <td class="py-3 text-right opacity-40 italic">In-Stay</td>
                    </tr>
                    <?php endforeach; ?>

                    <?php while($act = $acts_res->fetch_assoc()): ?>
                    <tr>
                        <td class="py-4 text-[#c5a059] italic lowercase">+ <?= $act['activity_name'] ?></td>
                        <td class="py-4 text-right opacity-40 italic">Activity</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-10 pt-10 border-t border-white/10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-end">
                <div class="space-y-4">
                    <?php if($booking['transport_needed']): ?>
                    <div class="p-4 border border-[#c5a059]/20 bg-[#c5a059]/5">
                        <p class="text-[9px] uppercase tracking-widest text-[#c5a059] mb-3 font-bold underline">Transport Arranged</p>
                        <p class="text-[10px] opacity-70">Pickup: <?= $booking['pickup_loc'] ?> (<?= $booking['pickup_time'] ?>)</p>
                        <p class="text-[10px] opacity-70 mt-1">Drop: <?= $booking['drop_loc'] ?> (<?= $booking['drop_time'] ?>)</p>
                    </div>
                    <?php endif; ?>
                    <p class="text-[8px] opacity-20 uppercase tracking-[0.3em]">This is an electronically generated receipt.</p>
                </div>

                <div class="bg-[#c5a059] text-black p-8 text-right shadow-xl">
                    <p class="text-[9px] uppercase font-bold tracking-[0.4em] mb-2">Total Paid</p>
                    <h2 class="playfair text-4xl italic font-bold"><?= $booking['currency'] ?> <?= number_format($booking['total_price'], 2) ?></h2>
                </div>
            </div>
        </div>

        <div class="mt-16 text-center">
            <p class="text-[10px] uppercase tracking-[0.5em] opacity-40 italic">Nature Nest Eco Resort | Sinharaja</p>
            <p class="text-[8px] opacity-20 mt-4 leading-loose uppercase tracking-widest">Please keep this image to show at the check-in lounge.</p>
        </div>
    </div>
    <div class="max-w-4xl w-full flex flex-wrap justify-between items-center mb-8 no-print gap-4">
    <a href="index.php" class="text-[10px] uppercase tracking-widest opacity-50 hover:opacity-100 transition">← Back to Sanctuary</a>
    
    <div class="flex gap-4">
        <button onclick="downloadAsImage()" class="bg-white/5 border border-white/10 text-white px-6 py-3 text-[10px] font-bold uppercase tracking-widest hover:bg-white/10 transition">
            <i class="fas fa-camera mr-2"></i> Save to Gallery
        </button>
        
        <a href="user_dashboard.php" class="bg-[#c5a059] text-black px-10 py-3 text-[10px] font-bold uppercase tracking-widest hover:bg-white transition shadow-2xl">
            <i class="fas fa-check-circle mr-2"></i> Done
        </a>
    </div>
 </div>

    <script>
        function downloadAsImage() {
            const element = document.getElementById('capture-area');
            
            // Image Capture Logic
            html2canvas(element, {
                backgroundColor: "#050805",
                scale: 3, // ඉහළම quality එක ලබා ගැනීමට
                useCORS: true,
                logging: false,
                width: element.offsetWidth,
                height: element.offsetHeight
            }).then(canvas => {
                const image = canvas.toDataURL("image/png");
                const link = document.createElement('a');
                link.href = image;
                link.download = 'NatureNest_Receipt_<?= $booking['secret_code'] ?>.png';
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
    </script>
</body>
</html>