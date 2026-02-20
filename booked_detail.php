<?php
session_start();
require_once 'db_config.php';

// 1. යූසර් ලොගින් පරීක්ෂාව
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id'];

// 2. දත්ත ලබා ගැනීම (අලුත්ම බුකින් ඉහළට එන ලෙස - Latest first)
$query = "SELECT b.*, p.package_name 
          FROM bookings b 
          LEFT JOIN packages p ON b.room_id = p.id 
          WHERE b.user_id = ? 
          ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations | Nature Nest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Playfair+Display:ital,wght@1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #050805; color: white; }
        .playfair { font-family: 'Playfair Display', serif; }
        .glass-card { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(197, 160, 89, 0.1); transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .glass-card:hover { border-color: #c5a059; background: rgba(197, 160, 89, 0.04); transform: translateY(-2px); }
        .cancelled-card { opacity: 0.5; filter: grayscale(0.8); border-color: rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body class="p-4 md:p-10">

    <div class="max-w-5xl mx-auto">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 border-b border-white/5 pb-8 gap-6">
            <div>
                <h1 class="playfair text-4xl italic text-[#c5a059]">Your Journeys</h1>
                <p class="text-[9px] uppercase tracking-[0.5em] opacity-40 mt-2">Manage your bespoke eco-sanctuary stays</p>
            </div>
            <div class="flex gap-4">
                <a href="book_rooms.php" class="text-[9px] font-bold uppercase tracking-widest border border-white/20 px-6 py-3 hover:bg-white hover:text-black transition">New Booking</a>
                <a href="user_dashboard.php" class="text-[9px] font-bold uppercase tracking-widest bg-white/5 px-6 py-3 hover:bg-white/10 transition">Dashboard</a>
            </div>
        </header>

        <div class="space-y-6">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): 
                    $is_cancelled = ($row['status'] == 'cancelled');
                ?>
                    <div class="glass-card p-6 md:p-8 rounded-sm flex flex-col md:flex-row justify-between items-center gap-6 <?= $is_cancelled ? 'cancelled-card' : '' ?>">
                        
                        <div class="flex-1 w-full md:w-auto text-center md:text-left">
                            <div class="flex flex-wrap justify-center md:justify-start items-center gap-4 mb-4">
                                <span class="text-[10px] <?= $is_cancelled ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-[#c5a059] text-black' ?> px-3 py-1 font-bold tracking-widest uppercase">
                                    #NN-00<?= $row['id'] ?> <?= $is_cancelled ? '• Cancelled' : '' ?>
                                </span>
                                <?php if(!$is_cancelled): ?>
                                    <span class="text-[10px] uppercase tracking-widest opacity-40">
                                        Secret Code: <span class="text-[#c5a059] font-bold"><?= $row['secret_code'] ?></span>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <h2 class="playfair text-3xl italic mb-2"><?= $row['package_name'] ?></h2>
                            <div class="flex flex-col md:flex-row gap-4 text-[10px] uppercase tracking-widest opacity-60">
                                <span><i class="far fa-calendar-alt mr-2 text-[#c5a059]"></i> <?= date('d M Y', strtotime($row['check_in'])) ?> — <?= date('d M Y', strtotime($row['check_out'])) ?></span>
                                <span><i class="far fa-moon mr-2 text-[#c5a059]"></i> <?= $row['nights'] ?> Nights</span>
                                <span><i class="far fa-user mr-2 text-[#c5a059]"></i> <?= $row['guests'] ?> Guests</span>
                            </div>
                        </div>

                        <div class="text-center md:text-right flex flex-col items-center md:items-end gap-4 border-t md:border-t-0 border-white/5 pt-6 md:pt-0 w-full md:w-auto">
                            <div>
                                <p class="text-[9px] uppercase tracking-widest opacity-40 mb-1">Total Value</p>
                                <p class="text-2xl font-semibold text-[#c5a059]"><?= $row['currency'] ?> <?= number_format($row['total_price'], 2) ?></p>
                            </div>

                            <div class="flex gap-3">
                                <?php if(!$is_cancelled): ?>
                                    <a href="thank_you.php?id=<?= $row['id'] ?>" class="px-5 py-2 border border-[#c5a059] text-[#c5a059] text-[9px] font-bold uppercase tracking-widest hover:bg-[#c5a059] hover:text-black transition">
                                        Receipt
                                    </a>
                                    <button onclick="confirmCancel(<?= $row['id'] ?>)" class="px-5 py-2 border border-red-500/40 text-red-500 text-[9px] font-bold uppercase tracking-widest hover:bg-red-500 hover:text-white transition">
                                        Cancel
                                    </button>
                                <?php else: ?>
                                    <span class="text-[9px] uppercase tracking-widest opacity-30 italic py-2">No actions available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-24 glass-card">
                    <div class="opacity-20 mb-6 italic playfair text-4xl">No stays found...</div>
                    <p class="text-[10px] uppercase tracking-[0.4em] opacity-40">Your future memories are waiting to be made.</p>
                    <a href="book_rooms.php" class="inline-block mt-8 bg-[#c5a059] text-black px-10 py-4 text-[10px] font-bold uppercase tracking-widest hover:bg-white transition shadow-2xl">Begin Reservation</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmCancel(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to cancel this sanctuary stay? This action is permanent.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c5a059',
                cancelButtonColor: '#1a1a1a',
                confirmButtonText: 'Yes, Cancel Stay',
                cancelButtonText: 'Keep Booking',
                background: '#0a0f0a',
                color: '#fff',
                customClass: {
                    popup: 'border border-white/10 rounded-none'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "cancel_booking.php?id=" + id;
                }
            })
        }
    </script>
</body>
</html>