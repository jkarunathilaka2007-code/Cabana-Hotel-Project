<?php
session_start();
require_once 'db_config.php';
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Package | Nature Nest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Cormorant+Garamond:ital,wght@1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #0a0f0a; color: white; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        .glass-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); padding: 25px; margin-bottom: 20px; }
        .glass-input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; outline: none; transition: 0.3s; width: 100%; font-size: 13px; }
        .glass-input:focus { border-color: #c5a059; background: rgba(255,255,255,0.1); }
        .price-label { color: #c5a059; font-size: 10px; font-weight: 600; letter-spacing: 1px; margin-bottom: 8px; display: block; }
        .add-btn { color: #c5a059; border: 1px dashed #c5a059; padding: 10px; width: 100%; text-align: center; cursor: pointer; transition: 0.3s; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; }
        .add-btn:hover { background: rgba(197, 160, 89, 0.1); }
    </style>
</head>
<body class="p-6 md:p-12">

    <div class="max-w-5xl mx-auto">
        <header class="mb-12 border-b border-[#c5a059]/30 pb-6">
            <h1 class="serif text-4xl italic text-[#c5a059]">Package Builder</h1>
            <p class="text-[10px] uppercase tracking-[0.5em] opacity-50 mt-2">Design Custom Experiences & Tiered Pricing</p>
        </header>

        <form action="save_package.php" method="POST" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                
                <div class="space-y-8">
                    <section>
                        <h3 class="text-[11px] uppercase tracking-widest font-bold text-[#c5a059] mb-6 tracking-[0.3em]">General Details</h3>
                        <div class="space-y-4">
                            <input type="text" name="p_name" placeholder="Package Name (e.g. Misty Mountain Suite)" class="glass-input" required>
                            <input type="number" name="max_guests" placeholder="Maximum Guests Allowed" class="glass-input" required>
                            <div class="space-y-2">
                                <label class="text-[9px] opacity-40 uppercase ml-1">Package Cover Image</label>
                                <input type="file" name="p_image" class="glass-input text-[10px]">
                            </div>
                        </div>
                    </section>

                    <section class="glass-card">
                        <h3 class="text-[11px] uppercase tracking-widest font-bold text-[#c5a059] mb-6 tracking-[0.3em]">Pricing Structure</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="price-label uppercase">Local Rate (LKR)</label>
                                <input type="number" name="price_lkr" placeholder="e.g. 25000" class="glass-input font-bold text-[#c5a059]" required>
                            </div>
                            <div>
                                <label class="price-label uppercase">Foreign Rate (USD)</label>
                                <input type="number" name="price_usd" placeholder="e.g. 120" class="glass-input font-bold text-[#c5a059]" required>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="space-y-8">
                    <section>
                        <h3 class="text-[11px] uppercase tracking-widest font-bold text-[#c5a059] mb-6 tracking-[0.3em]">Included Amenities</h3>
                        <p class="text-[9px] opacity-40 mb-4 italic uppercase tracking-wider">* Add amenities individually (e.g. Private Pool, Free WiFi)</p>
                        
                        <div id="amenities-container" class="space-y-3">
                            <div class="flex gap-2 items-center">
                                <input type="text" name="amenities[]" placeholder="Amenity Name" class="glass-input">
                                <i class="fas fa-check-circle opacity-20"></i>
                            </div>
                        </div>
                        
                        <div class="mt-4 add-btn" onclick="addAmenity()">
                            <i class="fas fa-plus mr-2"></i> Add Another Amenity
                        </div>
                    </section>
                </div>

            </div>

            <div class="mt-16 pt-10 border-t border-white/10 flex justify-between items-center">
                <a href="admin_dashboard.php" class="text-[10px] uppercase tracking-widest opacity-40 hover:opacity-100 transition">Discard Changes</a>
                <button type="submit" class="bg-[#c5a059] text-black px-12 py-4 text-[11px] font-bold uppercase tracking-[0.4em] hover:bg-white transition-all duration-700 shadow-2xl">
                    Create & Next Step <i class="fas fa-chevron-right ml-3"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        function addAmenity() {
            const container = document.getElementById('amenities-container');
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center relative';
            div.innerHTML = `
                <input type="text" name="amenities[]" placeholder="Amenity Name" class="glass-input">
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 opacity-50 hover:opacity-100 text-sm px-2">&times;</button>
            `;
            container.appendChild(div);
        }
    </script>

</body>
</html>