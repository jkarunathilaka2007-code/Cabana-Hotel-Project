<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$u_id = $_SESSION['user_id'];

// Profile Photo Logic
if (isset($_FILES['profile_img'])) {
    $folder = "uploads/profiles/";
    if (!file_exists($folder)) { mkdir($folder, 0777, true); }
    $file_name = time() . "_" . $_FILES['profile_img']['name'];
    if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $folder . $file_name)) {
        $conn->query("UPDATE users SET profile_pic = '$file_name' WHERE id = '$u_id'");
        header("Location: user_dashboard.php"); exit();
    }
}

$user = $conn->query("SELECT * FROM users WHERE id = '$u_id'")->fetch_assoc();
$profile_img = !empty($user['profile_pic']) ? "uploads/profiles/" . $user['profile_pic'] : "https://ui-avatars.com/api/?name=" . urlencode($user['fullname']) . "&background=c5a059&color=000";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Nature Nest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Cormorant+Garamond:ital,wght@1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #0a0f0a; color: #fff; overflow-x: hidden; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        
        /* Sidebar Responsive Logic */
        .sidebar { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); transform: translateX(-100%); z-index: 1000; }
        .sidebar.active { transform: translateX(0); }
        
        @media (min-width: 1024px) {
            .sidebar { transform: translateX(0); }
            .main-content { margin-left: 280px; }
        }

        .glass-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(197, 160, 89, 0.1); backdrop-filter: blur(10px); }
        .nav-link { transition: 0.3s; border-left: 3px solid transparent; opacity: 0.6; }
        .nav-link:hover, .nav-link.active { background: rgba(197, 160, 89, 0.08); border-left-color: #c5a059; opacity: 1; color: #c5a059; }
        
        /* Profile Drawer */
        .drawer { transform: translateX(100%); transition: 0.5s; z-index: 1100; }
        .drawer.active { transform: translateX(0); }
    </style>
</head>
<body>

    <div class="lg:hidden flex items-center justify-between p-5 bg-[#0a0f0a] border-b border-white/5 sticky top-0 z-50">
        <h2 class="serif text-xl italic text-[#c5a059]">Nature Nest</h2>
        <button onclick="toggleSidebar()" class="text-[#c5a059] text-2xl"><i class="fas fa-bars"></i></button>
    </div>

    <aside id="sidebar" class="sidebar fixed top-0 left-0 w-[280px] h-full bg-[#0d120d] border-r border-white/5 flex flex-col">
        <div class="p-10 text-center hidden lg:block">
            <h2 class="serif text-3xl italic text-[#c5a059]">Nature Nest</h2>
            <p class="text-[9px] uppercase tracking-[0.4em] opacity-40 mt-1">Luxury Eco Resort</p>
        </div>

        <nav class="flex-1 mt-10">
            <a href="user_dashboard.php" class="nav-link active flex items-center gap-4 py-4 px-8 text-[11px] uppercase tracking-widest">
                <i class="fas fa-th-large w-5"></i> Overview
            </a>
            <a href="booked_detail.php" class="nav-link flex items-center gap-4 py-4 px-8 text-[11px] uppercase tracking-widest">
                <i class="fas fa-bed w-5"></i> Book Rooms
            </a>
            <a href="food_menu.php" class="nav-link flex items-center gap-4 py-4 px-8 text-[11px] uppercase tracking-widest">
                <i class="fas fa-utensils w-5"></i> Food Packages
            </a>
            <a href="my_bookings.php" class="nav-link flex items-center gap-4 py-4 px-8 text-[11px] uppercase tracking-widest">
                <i class="fas fa-calendar-check w-5"></i> My Stays
            </a>
        </nav>

        <div class="p-8 border-t border-white/5">
            <a href="logout.php" class="flex items-center gap-4 text-red-500 text-[11px] uppercase tracking-widest hover:opacity-70">
                <i class="fas fa-sign-out-alt w-5"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="main-content min-h-screen p-6 lg:p-12">
        
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
            <div>
                <h1 class="serif text-4xl italic text-white mb-2">My Sanctuary</h1>
                <p class="text-[10px] uppercase tracking-[0.5em] text-[#c5a059]">Guest Identification: NN-<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></p>
            </div>
            
            <div onclick="toggleDrawer()" class="flex items-center gap-4 bg-white/5 p-2 pr-6 rounded-full border border-white/10 cursor-pointer hover:border-[#c5a059]/50 transition">
                <img src="<?= $profile_img ?>" class="w-12 h-12 rounded-full object-cover border border-[#c5a059]">
                <div class="hidden sm:block">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-0.5"><?= $user['fullname'] ?></p>
                    <p class="text-[8px] opacity-40 uppercase tracking-tighter italic text-[#c5a059]">View Profile Info</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4 flex flex-col gap-8">
                <div class="glass-card p-10 text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-[#c5a059]"></div>
                    <div class="relative inline-block group mb-6">
                        <img src="<?= $profile_img ?>" class="w-32 h-32 rounded-full mx-auto object-cover border-2 border-[#c5a059]/30 p-1">
                        <form id="imgForm" action="" method="POST" enctype="multipart/form-data">
                            <label for="imgInp" class="absolute bottom-1 right-1 bg-[#c5a059] text-black w-8 h-8 rounded-full flex items-center justify-center cursor-pointer shadow-xl hover:scale-110 transition">
                                <i class="fas fa-camera text-xs"></i>
                            </label>
                            <input type="file" name="profile_img" id="imgInp" class="hidden" onchange="document.getElementById('imgForm').submit()">
                        </form>
                    </div>
                    <h3 class="serif text-2xl mb-1"><?= $user['fullname'] ?></h3>
                    <p class="text-[10px] text-[#c5a059] uppercase tracking-widest opacity-60 italic mb-8"><?= $user['nationality'] ?></p>
                    
                    <button onclick="toggleDrawer()" class="w-full py-3 border border-[#c5a059]/30 text-[9px] uppercase tracking-[0.3em] hover:bg-[#c5a059] hover:text-black transition">Manage Account</button>
                </div>

                <div class="glass-card p-8">
                    <h4 class="text-[10px] uppercase tracking-widest text-[#c5a059] mb-6 border-b border-white/5 pb-2">Stay Statistics</h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] opacity-40 uppercase">Total Bookings</span>
                            <span class="serif text-xl italic">0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] opacity-40 uppercase">Loyalty Level</span>
                            <span class="text-[9px] text-[#c5a059] border border-[#c5a059] px-2 py-0.5 rounded">Silver</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 space-y-8">
                <div class="glass-card p-10">
                    <h3 class="serif text-2xl italic mb-8 flex items-center gap-4">
                        Personal Information 
                        <span class="h-[1px] flex-1 bg-white/5"></span>
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-12">
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase tracking-[0.3em] opacity-40">Registered Email</label>
                            <p class="text-sm font-light text-white/90 underline decoration-[#c5a059]/30 underline-offset-8"><?= $user['email'] ?></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase tracking-[0.3em] opacity-40">Contact Telephone</label>
                            <p class="text-sm font-light text-white/90 underline decoration-[#c5a059]/30 underline-offset-8"><?= $user['phone'] ?></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase tracking-[0.3em] opacity-40">Nationality / Origin</label>
                            <p class="text-sm font-light text-white/90 underline decoration-[#c5a059]/30 underline-offset-8 uppercase"><?= $user['nationality'] ?></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase tracking-[0.3em] opacity-40">Member Since</label>
                            <p class="text-sm font-light text-white/90 underline decoration-[#c5a059]/30 underline-offset-8"><?= date('F d, Y', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-10 bg-[#c5a059]/5 border-[#c5a059]/20">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="serif text-2xl italic">Ready for your next stay?</h3>
                            <p class="text-[10px] opacity-60 mt-2 uppercase tracking-widest">Explore our newest nature-blended suites</p>
                        </div>
                        <a href="booked_detail.php" class="bg-[#c5a059] text-black px-8 py-4 text-[10px] font-bold uppercase tracking-widest hover:bg-white transition whitespace-nowrap">My Bookings</a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <aside id="drawer" class="drawer fixed top-0 right-0 w-full sm:w-[400px] h-full bg-[#0a0f0a] border-l border-[#c5a059]/20 p-10 flex flex-col shadow-2xl">
        <div class="flex justify-between items-center mb-12">
            <h3 class="serif text-2xl italic text-[#c5a059]">Account Settings</h3>
            <button onclick="toggleDrawer()" class="text-3xl font-light hover:text-[#c5a059]">&times;</button>
        </div>
        
        <div class="space-y-8 flex-1">
            <div class="p-6 bg-white/5 border border-white/5">
                <label class="text-[9px] uppercase opacity-40 block mb-2">Edit Full Name</label>
                <input type="text" value="<?= $user['fullname'] ?>" class="w-full bg-transparent border-b border-[#c5a059]/30 py-2 text-sm outline-none focus:border-[#c5a059]">
            </div>
            <div class="p-6 bg-white/5 border border-white/5">
                <label class="text-[9px] uppercase opacity-40 block mb-2">Change Contact</label>
                <input type="text" value="<?= $user['phone'] ?>" class="w-full bg-transparent border-b border-[#c5a059]/30 py-2 text-sm outline-none focus:border-[#c5a059]">
            </div>
            <p class="text-[9px] opacity-30 italic leading-relaxed">Note: Your email and nationality are locked for security. Please contact the concierge for changes.</p>
        </div>

        <button class="w-full py-4 bg-[#c5a059] text-black text-[10px] font-bold uppercase tracking-[0.4em] hover:bg-white transition mt-auto shadow-2xl">Update Profile</button>
    </aside>

    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/80 z-[900] hidden"></div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }
        function toggleDrawer() {
            document.getElementById('drawer').classList.toggle('active');
        }
    </script>
</body>
</html>