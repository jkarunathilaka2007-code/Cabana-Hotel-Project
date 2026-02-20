<?php 
session_start(); 

// Admin කෙනෙක් නම් Dashboard එකට යවන්න
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nature Nest Cabana | High-End Tropical Escape</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@200;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1a2e1a;
            --accent: #c5a059;
        }
        body { font-family: 'Montserrat', sans-serif; background-color: #fdfdfb; overflow-x: hidden; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        
        html { scroll-behavior: smooth; }

        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.45)), url('hero.png'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .glass-nav {
            backdrop-filter: blur(15px);
            background: rgba(26, 46, 26, 0.05);
            transition: all 0.4s ease;
        }
        .glass-nav.scrolled {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .btn-shine {
            position: relative;
            overflow: hidden;
        }
        .btn-shine::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255,255,255,0.2);
            transform: rotate(45deg);
            transition: 0.5s;
        }
        .btn-shine:hover::after {
            left: 120%;
        }
    </style>
</head>
<body>

    <nav id="navbar" class="fixed w-full z-50 px-6 md:px-12 py-6 flex justify-between items-center glass-nav text-white">
        <div class="text-xl md:text-2xl font-bold tracking-[4px] serif">NATURE NEST</div>
        
        <div class="hidden lg:flex items-center space-x-8 text-[10px] uppercase tracking-[0.3em] font-semibold">
            <a href="#about" class="hover:text-[#c5a059] transition">Story</a>
            <a href="#rooms" class="hover:text-[#c5a059] transition">Cabanas</a>
            <a href="#gallery" class="hover:text-[#c5a059] transition">Gallery</a>
            
            <span class="h-4 w-[1px] bg-white/30 mx-2"></span>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user_dashboard.php" class="bg-[#c5a059] text-white px-6 py-2 rounded-sm btn-shine italic serif lowercase tracking-normal text-sm">Dashboard</a>
                <a href="auth_handler.php?logout=true" class="hover:text-red-400 transition" title="Logout"><i class="fas fa-power-off"></i></a>
            <?php else: ?>
                <a href="login.php" class="hover:opacity-70 transition">Login</a>
                <a href="register.php" class="bg-[#c5a059] text-white px-6 py-2 rounded-sm btn-shine">Register</a>
            <?php endif; ?>
        </div>

        <button class="lg:hidden text-2xl" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <div id="mobileMenu" class="fixed inset-0 bg-black z-[60] flex flex-col items-center justify-center space-y-8 text-white hidden">
        <button class="absolute top-10 right-10 text-3xl" onclick="toggleMenu()">&times;</button>
        <a href="#about" onclick="toggleMenu()" class="text-2xl serif italic">The Story</a>
        <a href="#rooms" onclick="toggleMenu()" class="text-2xl serif italic">Cabanas</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="user_dashboard.php" class="text-xl font-light tracking-widest text-[#c5a059]">DASHBOARD</a>
            <a href="auth_handler.php?logout=true" class="text-xs opacity-50">LOGOUT</a>
        <?php else: ?>
            <a href="login.php" class="text-xl font-light tracking-widest">LOGIN</a>
            <a href="register.php" class="bg-[#c5a059] px-8 py-3 rounded text-sm tracking-widest">REGISTER</a>
        <?php endif; ?>
    </div>

    <header class="hero flex items-center justify-center text-center text-white relative">
        <div data-aos="zoom-out" data-aos-duration="2000">
            <span class="block text-[10px] md:text-[12px] uppercase tracking-[0.5em] mb-4 opacity-80">Mountains of Sri Lanka</span>
            <h1 class="text-5xl md:text-[100px] serif leading-none mb-8 italic">Breath of the Mist</h1>
            
            <?php if (isset($_SESSION['user_name'])): ?>
                <p class="mb-6 text-[10px] tracking-[0.4em] text-[#c5a059] uppercase font-bold">Welcome back, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?></p>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row justify-center gap-6">
                <a href="#rooms" class="bg-white text-black px-10 py-4 hover:bg-[#c5a059] hover:text-white transition-all duration-500 uppercase text-[10px] tracking-[0.3em] font-bold shadow-xl">Explore Cabanas</a>
            </div>
        </div>
    </header>

    <section class="max-w-6xl mx-auto -mt-16 relative z-40 px-4" data-aos="fade-up">
        <div class="bg-white shadow-2xl rounded-sm p-4 md:p-2 border border-stone-100">
            <div class="grid grid-cols-1 md:grid-cols-4 items-center">
                <div class="p-6 md:border-r border-gray-100">
                    <p class="text-[9px] uppercase tracking-widest font-bold text-gray-400 mb-2">Check In</p>
                    <input type="date" class="w-full outline-none text-xs font-semibold">
                </div>
                <div class="p-6 md:border-r border-gray-100">
                    <p class="text-[9px] uppercase tracking-widest font-bold text-gray-400 mb-2">Check Out</p>
                    <input type="date" class="w-full outline-none text-xs font-semibold">
                </div>
                <div class="p-6 md:border-r border-gray-100">
                    <p class="text-[9px] uppercase tracking-widest font-bold text-gray-400 mb-2">Accommodation</p>
                    <select class="w-full outline-none text-xs font-semibold bg-transparent">
                        <option>Canopy Suite</option>
                        <option>Mist Hideaway</option>
                    </select>
                </div>
                <button class="bg-[#1a2e1a] text-white py-8 md:py-10 hover:bg-[#2c4e2c] transition font-bold uppercase text-[10px] tracking-[0.4em]">Search Availability</button>
            </div>
        </div>
    </section>

    <section id="about" class="py-32 px-6 max-w-7xl mx-auto overflow-hidden">
        <div class="grid md:grid-cols-2 gap-20 items-center">
            <div data-aos="fade-right">
                <img src="about.png" class="w-full h-[500px] object-cover rounded-sm shadow-2xl">
            </div>
            <div data-aos="fade-left">
                <h3 class="text-4xl md:text-5xl serif mb-6 italic">Where Time Stands Still.</h3>
                <p class="text-gray-500 leading-loose mb-8 font-light italic">Nature Nest is perched at 1200m above sea level, offering panoramic mountain views. Here, luxury is the silence of the clouds and the warmth of Sri Lankan tradition.</p>
                <a href="register.php" class="text-[10px] uppercase tracking-[0.3em] font-bold border-b-2 border-[#c5a059] pb-2">Join our loyalty program</a>
            </div>
        </div>
    </section>

    <section id="rooms" class="bg-stone-50 py-32 px-6">
        <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12">
            <div class="group" data-aos="fade-up">
                <div class="overflow-hidden h-[400px] mb-6 shadow-lg">
                    <img src="room1.png" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-1000">
                </div>
                <h4 class="serif text-3xl italic">The Canopy Suite</h4>
                <p class="text-[10px] uppercase tracking-widest text-gray-400 mt-2">Starting from $120/Night</p>
            </div>
            <div class="group" data-aos="fade-up" data-aos-delay="200">
                <div class="overflow-hidden h-[400px] mb-6 shadow-lg">
                    <img src="room2.png" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-1000">
                </div>
                <h4 class="serif text-3xl italic">Mist Hideaway</h4>
                <p class="text-[10px] uppercase tracking-widest text-gray-400 mt-2">Starting from $95/Night</p>
            </div>
        </div>
    </section>

    <footer class="bg-[#1a2e1a] text-white py-20 px-8 text-center">
        <p class="serif text-3xl mb-8 opacity-80 italic">Nature Nest Cabana</p>
        <div class="flex justify-center space-x-8 opacity-50 text-xs tracking-widest mb-10">
            <a href="#">INSTAGRAM</a>
            <a href="#">FACEBOOK</a>
            <a href="#">WHATSAPP</a>
        </div>
        <p class="text-[9px] opacity-20 tracking-[0.5em]">&copy; 2026 NATURE NEST SRI LANKA</p>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.pageYOffset > 50) {
                nav.classList.add("scrolled", "text-black");
                nav.classList.remove("text-white");
            } else {
                nav.classList.remove("scrolled", "text-black");
                nav.classList.add("text-white");
            }
        };

        function toggleMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>