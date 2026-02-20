<?php
session_start();
require_once 'db_config.php';

// 1. Admin ආරක්ෂාව
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

// 2. Statistics ගණනය කිරීම (Dynamic Queries)

// මුළු පරිශීලකයින් ගණන
$total_users = $conn->query("SELECT id FROM users")->num_rows;

// දැනට ක්‍රියාත්මක බුකින්ස් ගණන (Status එක cancelled නොවන ඒවා)
$active_bookings = $conn->query("SELECT id FROM bookings WHERE status != 'cancelled'")->num_rows;

// මුළු ආදායම (Total Revenue) - Currency එක LKR හෝ USD අනුව ගණනය කිරීම
// මෙතනදී අපි සරලව total_price එකේ එකතුව ගනිමු
$revenue_res = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status != 'cancelled'");
$revenue_data = $revenue_res->fetch_assoc();
$total_revenue = number_format($revenue_data['total'] ?? 0, 2);

// අද දින කෑම වේල් ගණන (Kitchen Forecast එකෙන් කොටසක්)
$today = date('Y-m-d');
$today_meals_res = $conn->query("SELECT b.guests 
                                 FROM booking_meals bm 
                                 JOIN bookings b ON bm.booking_id = b.id 
                                 WHERE b.status != 'cancelled' 
                                 AND b.check_in <= '$today' AND b.check_out >= '$today'");
$today_pax = 0;
while($m = $today_meals_res->fetch_assoc()) { $today_pax += $m['guests']; }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Panel | Nature Nest Sanctuary</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Cormorant+Garamond:ital,wght@1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #040704; color: #e5e7eb; overflow-x: hidden; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        .sidebar { background: #080d08; border-right: 1px solid rgba(197, 160, 89, 0.15); }
        .glass-card { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(197, 160, 89, 0.1); transition: all 0.4s ease; }
        .glass-card:hover { border-color: #c5a059; background: rgba(197, 160, 89, 0.05); transform: translateY(-5px); }
        .nav-link { transition: all 0.3s; border-left: 3px solid transparent; }
        .nav-link:hover, .nav-link.active { border-left-color: #c5a059; background: rgba(197, 160, 89, 0.05); color: #c5a059; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 sidebar hidden md:flex flex-col sticky top-0 h-screen">
        <div class="p-10 border-b border-[#c5a059]/10">
            <h1 class="serif text-2xl italic tracking-widest text-[#c5a059]">Nature Nest</h1>
            <p class="text-[8px] uppercase tracking-[0.4em] opacity-40 mt-2">Executive Command</p>
        </div>
        
        <nav class="flex-1 p-6 space-y-2">
            <a href="admin_dashboard.php" class="nav-link active flex items-center gap-4 p-4 text-[10px] uppercase tracking-widest font-bold">
                <i class="fas fa-chart-line w-5"></i> Overview
            </a>
            <a href="booked_data.php" class="nav-link flex items-center gap-4 p-4 text-[10px] uppercase tracking-widest opacity-60">
                <i class="fas fa-calendar-alt w-5"></i> Reservations
            </a>
            <a href="create_packagers.php" class="nav-link flex items-center gap-4 p-4 text-[10px] uppercase tracking-widest opacity-60">
                <i class="fas fa-box w-5"></i> Room Packages
            </a>
            <a href="create_food_menu.php" class="nav-link flex items-center gap-4 p-4 text-[10px] uppercase tracking-widest opacity-60">
                <i class="fas fa-utensils w-5"></i> Dining Menus
            </a>
            <a href="users_manage.php" class="nav-link flex items-center gap-4 p-4 text-[10px] uppercase tracking-widest opacity-60">
                <i class="fas fa-user-friends w-5"></i> Guest Profiles
            </a>
        </nav>

        <div class="p-8 border-t border-[#c5a059]/10">
            <a href="auth_handler.php?logout=true" class="flex items-center gap-4 text-red-500/60 hover:text-red-500 text-[10px] uppercase tracking-widest font-bold transition">
                <i class="fas fa-sign-out-alt w-5"></i> Terminate Session
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8 md:p-16">
        <header class="flex justify-between items-start mb-16">
            <div>
                <h2 class="text-[10px] uppercase tracking-[0.6em] text-[#c5a059] font-bold mb-3">Administrator Console</h2>
                <h1 class="serif text-5xl italic">Greetings, Manager.</h1>
            </div>
            <div class="text-right hidden md:block">
                <p class="text-sm font-light opacity-80"><?php echo date('l, jS F'); ?></p>
                <p class="text-[10px] opacity-30 uppercase tracking-[0.2em] mt-1">Sanctuary Time</p>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
            <div class="glass-card p-10 rounded-sm shadow-xl">
                <p class="text-[9px] uppercase tracking-widest opacity-40 mb-6">Total Guests</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-light"><?php echo $total_users; ?></h3>
                    <i class="fas fa-users text-[#c5a059] text-xs opacity-20"></i>
                </div>
            </div>
            
            <div class="glass-card p-10 rounded-sm shadow-xl border-l-2 border-l-[#c5a059]">
                <p class="text-[9px] uppercase tracking-widest opacity-40 mb-6">Active Bookings</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-light text-[#c5a059]"><?php echo $active_bookings; ?></h3>
                </div>
            </div>

            <div class="glass-card p-10 rounded-sm shadow-xl">
                <p class="text-[9px] uppercase tracking-widest opacity-40 mb-6">Total Revenue</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-3xl font-light tracking-tighter"><?php echo $total_revenue; ?></h3>
                    <span class="text-[9px] opacity-40 uppercase">LKR</span>
                </div>
            </div>

            <div class="glass-card p-10 rounded-sm shadow-xl">
                <p class="text-[9px] uppercase tracking-widest opacity-40 mb-6">Meals Today</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-light"><?php echo $today_pax; ?></h3>
                    <span class="text-[9px] opacity-40 uppercase">Pax</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <div class="lg:col-span-2 space-y-8">
                <div class="flex justify-between items-end border-b border-white/10 pb-4">
                    <h4 class="text-[10px] uppercase tracking-[0.3em] font-black">Recent Guest Registry</h4>
                    <a href="users_manage.php" class="text-[9px] text-[#c5a059] hover:text-white transition uppercase tracking-widest">Explore All <i class="fas fa-chevron-right ml-1"></i></a>
                </div>
                
                <div class="space-y-4">
                    <?php
                    $latest_users = $conn->query("SELECT fullname, email, nationality, created_at FROM users ORDER BY id DESC LIMIT 5");
                    if($latest_users->num_rows > 0):
                        while($u = $latest_users->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-6 glass-card group">
                            <div class="flex items-center gap-6">
                                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center border border-white/10 text-[#c5a059]">
                                    <?php echo strtoupper(substr($u['fullname'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold tracking-wide group-hover:text-[#c5a059] transition"><?php echo $u['fullname']; ?></p>
                                    <p class="text-[10px] opacity-40 lowercase"><?php echo $u['email']; ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[8px] border border-white/10 px-3 py-1 uppercase tracking-widest"><?php echo $u['nationality']; ?></span>
                                <p class="text-[8px] opacity-20 mt-2 uppercase tracking-tighter"><?php echo date('M d', strtotime($u['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endwhile; 
                    else: ?>
                        <div class="p-10 text-center opacity-20 italic text-sm">Waiting for new arrivals...</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-8">
                <h4 class="text-[10px] uppercase tracking-[0.3em] font-black border-b border-white/10 pb-4">Management Tools</h4>
                <div class="grid gap-4">
                    <a href="booked_data.php" class="bg-[#c5a059] text-black p-6 rounded-sm text-center group relative overflow-hidden transition">
                        <span class="relative z-10 text-[10px] font-black uppercase tracking-widest">Launch Meal Tracker</span>
                        <div class="absolute inset-0 bg-white translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                    </a>
                    
                    <a href="create_packagers.php" class="glass-card p-6 text-center text-[10px] font-bold uppercase tracking-widest hover:border-[#c5a059]">
                        Edit Pricing Models
                    </a>

                    <div class="p-8 glass-card">
                        <h5 class="text-[9px] uppercase tracking-widest mb-4 opacity-40">System Health</h5>
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                            <span class="text-[10px] font-bold uppercase">Database Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Optional: Admin dashboard interactivity can go here
    </script>
</body>
</html>