<?php
session_start();
require_once 'db_config.php';
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food Package Builder | Nature Nest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Cormorant+Garamond:ital,wght@1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #0a0f0a; color: white; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        .glass-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 30px; margin-bottom: 40px; }
        .glass-input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 10px; outline: none; transition: 0.3s; font-size: 13px; width: 100%; }
        .glass-input:focus { border-color: #c5a059; background: rgba(255,255,255,0.1); }
        .sub-item-card { background: rgba(0,0,0,0.3); border-left: 2px solid #c5a059; padding: 15px; margin-top: 10px; }
        .upload-btn { font-size: 10px; color: #c5a059; cursor: pointer; border: 1px solid rgba(197,160,89,0.3); padding: 5px 10px; display: inline-block; }
    </style>
</head>
<body class="p-6 md:p-12">

    <div class="max-w-6xl mx-auto">
        <header class="mb-12 border-b border-[#c5a059]/30 pb-6">
            <h1 class="serif text-4xl italic text-[#c5a059]">Food Package Builder</h1>
            <p class="text-[10px] uppercase tracking-[0.5em] opacity-50 mt-2">Create curated meal experiences (Breakfast, Dinner, etc.)</p>
        </header>

        <form action="save_food_package.php" method="POST" enctype="multipart/form-data">
            
            <div class="glass-card">
                <h3 class="text-[11px] uppercase tracking-[0.3em] text-[#c5a059] mb-6">Step 1: Food Package Identity</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label class="text-[9px] uppercase opacity-40">Food Package Name</label>
                        <input type="text" name="fp_name" placeholder="e.g. Traditional Sri Lankan Breakfast" class="glass-input" required>
                    </div>
                    <div class="space-y-4">
                        <label class="text-[9px] uppercase opacity-40">Package Cover Image</label>
                        <input type="file" name="fp_cover" class="glass-input text-[10px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                    <div class="space-y-4">
                        <label class="text-[9px] uppercase opacity-40">Package Price (Local LKR)</label>
                        <input type="number" name="fp_price_lkr" placeholder="2500" class="glass-input text-[#c5a059] font-bold">
                    </div>
                    <div class="space-y-4">
                        <label class="text-[9px] uppercase opacity-40">Package Price (Foreign USD)</label>
                        <input type="number" name="fp_price_usd" placeholder="10" class="glass-input text-[#c5a059] font-bold">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest text-[#c5a059] border-b border-white/10 pb-2">Included Foods</h4>
                    <div id="foods-container" class="space-y-3">
                        <div class="sub-item-card">
                            <input type="text" name="food_names[]" placeholder="Item Name (e.g. Milk Rice)" class="glass-input mb-2">
                            <input type="file" name="food_images[]" class="text-[9px] opacity-60">
                        </div>
                    </div>
                    <button type="button" onclick="addItem('foods-container', 'food_names', 'food_images')" class="text-[9px] uppercase tracking-widest opacity-60 hover:opacity-100">+ Add Food</button>
                </div>

                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest text-blue-400 border-b border-white/10 pb-2">Included Drinks</h4>
                    <div id="drinks-container" class="space-y-3">
                        <div class="sub-item-card" style="border-left-color: #60a5fa;">
                            <input type="text" name="drink_names[]" placeholder="Drink Name (e.g. Ceylon Tea)" class="glass-input mb-2">
                            <input type="file" name="drink_images[]" class="text-[9px] opacity-60">
                        </div>
                    </div>
                    <button type="button" onclick="addItem('drinks-container', 'drink_names', 'drink_images')" class="text-[9px] uppercase tracking-widest opacity-60 hover:opacity-100">+ Add Drink</button>
                </div>

                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest text-green-400 border-b border-white/10 pb-2">Complimentary</h4>
                    <div id="free-container" class="space-y-3">
                        <div class="sub-item-card" style="border-left-color: #4ade80;">
                            <input type="text" name="free_names[]" placeholder="Free Item (e.g. Fruits)" class="glass-input mb-2">
                            <input type="file" name="free_images[]" class="text-[9px] opacity-60">
                        </div>
                    </div>
                    <button type="button" onclick="addItem('free-container', 'free_names', 'free_images')" class="text-[9px] uppercase tracking-widest opacity-60 hover:opacity-100">+ Add Freebie</button>
                </div>

            </div>

            <div class="mt-16 pt-10 border-t border-white/10 flex justify-between items-center">
                <a href="admin_dashboard.php" class="text-[10px] uppercase tracking-widest opacity-40">Cancel</a>
                <button type="submit" class="bg-[#c5a059] text-black px-12 py-4 text-[11px] font-bold uppercase tracking-[0.4em] hover:bg-white transition-all duration-700">
                    Publish Food Package <i class="fas fa-utensils ml-3"></i>
                </button>
            </div>
        </form>
    </div>

    

    <script>
        function addItem(containerId, nameAttr, imgAttr) {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            let color = containerId === 'drinks-container' ? '#60a5fa' : (containerId === 'free-container' ? '#4ade80' : '#c5a059');
            
            div.className = 'sub-item-card relative';
            div.style.borderLeftColor = color;
            div.innerHTML = `
                <button type="button" onclick="this.parentElement.remove()" class="absolute top-2 right-2 text-red-500 opacity-50">&times;</button>
                <input type="text" name="${nameAttr}[]" placeholder="Item Name" class="glass-input mb-2">
                <input type="file" name="${imgAttr}[]" class="text-[9px] opacity-60">
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>