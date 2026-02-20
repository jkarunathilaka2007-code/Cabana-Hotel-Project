<?php
session_start();
require_once 'db_config.php';

// Admin ලොගින් එක චෙක් කිරීම
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }

// --- 1. OFFER එකක් SAVE කිරීමේ LOGIC එක ---
if (isset($_POST['add_offer'])) {
    $type = $_POST['p_type'];
    $p_id = $_POST['p_id'];
    $discount = $_POST['discount'];
    $from = $_POST['from_date'];
    $to = $_POST['to_date'];

    $stmt = $conn->prepare("INSERT INTO offers (package_type, package_id, discount_percentage, valid_from, valid_to) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siiss", $type, $p_id, $discount, $from, $to);
    $stmt->execute();
    header("Location: settings.php?msg=offer_added");
}

// --- 2. ACTIVITY එකක් SAVE කිරීමේ LOGIC එක ---
if (isset($_POST['add_activity'])) {
    $a_name = mysqli_real_escape_string($conn, $_POST['a_name']);
    $a_lkr = $_POST['a_lkr'];
    $a_usd = $_POST['a_usd'];
    
    // Image Upload
    $target_dir = "uploads/activities/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    $file_name = time() . "_" . basename($_FILES["a_img"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["a_img"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO activities (activity_name, price_lkr, price_usd, activity_image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdds", $a_name, $a_lkr, $a_usd, $target_file);
        $stmt->execute();
        header("Location: settings.php?msg=activity_added");
    }
}

// Dropdowns සඳහා දත්ත ලබාගැනීම
$rooms = $conn->query("SELECT id, package_name FROM packages");
$foods = $conn->query("SELECT id, fp_name FROM food_packages");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Nature Nest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Cormorant+Garamond:ital,wght@1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #050805; color: white; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        .glass-card { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(197, 160, 89, 0.1); }
        .glass-input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; width: 100%; outline: none; transition: 0.3s; color: white; font-size: 13px; }
        .glass-input:focus { border-color: #c5a059; background: rgba(255,255,255,0.1); }
        select option { background: #0a0f0a; color: white; }
    </style>
</head>
<body class="p-4 md:p-10">

    <div class="max-w-7xl mx-auto">
        <header class="mb-12 border-b border-[#c5a059]/20 pb-8 flex justify-between items-end">
            <div>
                <h1 class="serif text-5xl italic text-[#c5a059]">Advanced Settings</h1>
                <p class="text-[10px] uppercase tracking-[0.5em] opacity-40 mt-2">Manage Special Offers & Extra Activities</p>
            </div>
            <a href="admin_dashboard.php" class="text-[10px] uppercase tracking-widest border border-white/20 px-6 py-2 hover:bg-white hover:text-black transition">Back to Dashboard</a>
        </header>

        <section class="mb-20">
            <div class="flex items-center gap-4 mb-10">
                <h2 class="serif text-3xl italic">01. Seasonal Offers</h2>
                <div class="h-[1px] flex-1 bg-[#c5a059]/20"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <div class="glass-card p-8 rounded-sm">
                    <h3 class="serif text-xl mb-6 text-[#c5a059]">Create Promotion</h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="text-[9px] uppercase opacity-40 block mb-1">Target Category</label>
                            <select name="p_type" id="p_type" class="glass-input" onchange="updateDropdown()" required>
                                <option value="room">Room Packages</option>
                                <option value="food">Food Packages</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[9px] uppercase opacity-40 block mb-1">Select Package</label>
                            <select name="p_id" id="p_id" class="glass-input" required></select>
                        </div>
                        <div>
                            <label class="text-[9px] uppercase opacity-40 block mb-1">Discount Percentage (%)</label>
                            <input type="number" name="discount" placeholder="e.g. 15" class="glass-input" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[9px] uppercase opacity-40 block mb-1">Valid From</label>
                                <input type="date" name="from_date" class="glass-input" required>
                            </div>
                            <div>
                                <label class="text-[9px] uppercase opacity-40 block mb-1">Valid Until</label>
                                <input type="date" name="to_date" class="glass-input" required>
                            </div>
                        </div>
                        <button type="submit" name="add_offer" class="w-full bg-[#c5a059] text-black py-4 text-[10px] font-bold uppercase tracking-widest hover:bg-white transition mt-4">Launch Promotion</button>
                    </form>
                </div>

                <div class="lg:col-span-2 glass-card p-8 rounded-sm overflow-hidden">
                    <h3 class="serif text-xl mb-6 opacity-60">Live Promotions</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[11px] tracking-wide">
                            <thead class="bg-white/5 uppercase opacity-50">
                                <tr>
                                    <th class="p-4">Package</th>
                                    <th class="p-4">Discount</th>
                                    <th class="p-4">Validity Period</th>
                                    <th class="p-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php
                                $offers = $conn->query("SELECT * FROM offers ORDER BY id DESC");
                                while($off = $offers->fetch_assoc()):
                                    $pid = $off['package_id'];
                                    $p_name = ($off['package_type'] == 'room') 
                                        ? $conn->query("SELECT package_name FROM packages WHERE id = $pid")->fetch_assoc()['package_name']
                                        : $conn->query("SELECT fp_name FROM food_packages WHERE id = $pid")->fetch_assoc()['fp_name'];
                                ?>
                                <tr>
                                    <td class="p-4 font-semibold italic"><?= $p_name ?> <span class="text-[8px] opacity-40 block uppercase not-italic"><?= $off['package_type'] ?></span></td>
                                    <td class="p-4 text-[#c5a059] font-bold text-lg"><?= $off['discount_percentage'] ?>%</td>
                                    <td class="p-4 opacity-60"><?= $off['valid_from'] ?> to <?= $off['valid_to'] ?></td>
                                    <td class="p-4 text-center"><a href="#" class="text-red-500 hover:text-white transition"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="flex items-center gap-4 mb-10">
                <h2 class="serif text-3xl italic">02. Extra Activities</h2>
                <div class="h-[1px] flex-1 bg-[#c5a059]/20"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <div class="glass-card p-8 rounded-sm">
                    <h3 class="serif text-xl mb-6 text-[#c5a059]">New Experience</h3>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="text" name="a_name" placeholder="Activity Title" class="glass-input" required>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="number" name="a_lkr" placeholder="LKR Price" class="glass-input" required>
                            <input type="number" name="a_usd" placeholder="USD Price" class="glass-input" required>
                        </div>
                        <div class="border border-white/10 p-4 rounded-sm">
                            <label class="text-[9px] uppercase opacity-40 block mb-2">Cover Image</label>
                            <input type="file" name="a_img" class="text-[10px] w-full" required>
                        </div>
                        <button type="submit" name="add_activity" class="w-full border border-[#c5a059] text-[#c5a059] py-4 text-[10px] font-bold uppercase tracking-widest hover:bg-[#c5a059] hover:text-black transition mt-4">Add Activity</button>
                    </form>
                </div>

                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php
                    $activities = $conn->query("SELECT * FROM activities ORDER BY id DESC");
                    while($act = $activities->fetch_assoc()):
                    ?>
                    <div class="glass-card p-4 flex gap-6 items-center group">
                        <div class="w-24 h-24 overflow-hidden rounded-sm">
                            <img src="<?= $act['activity_image'] ?>" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition duration-500">
                        </div>
                        <div class="flex-1">
                            <h4 class="serif text-xl italic"><?= $act['activity_name'] ?></h4>
                            <p class="text-[10px] text-[#c5a059] font-bold mt-1 uppercase tracking-widest">LKR <?= number_format($act['price_lkr']) ?> | $<?= $act['price_usd'] ?></p>
                        </div>
                        <button class="text-white/20 hover:text-red-500 transition px-4"><i class="fas fa-times"></i></button>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
    </div>

    

    <script>
        // Dropdown Logic
        const rooms = <?php $r_data = []; $rooms->data_seek(0); while($r = $rooms->fetch_assoc()){ $r_data[] = $r; } echo json_encode($r_data); ?>;
        const foods = <?php $f_data = []; $foods->data_seek(0); while($f = $foods->fetch_assoc()){ $f_data[] = $f; } echo json_encode($f_data); ?>;

        function updateDropdown() {
            const type = document.getElementById('p_type').value;
            const pSelect = document.getElementById('p_id');
            pSelect.innerHTML = '';
            const data = (type === 'room') ? rooms : foods;
            const key = (type === 'room') ? 'package_name' : 'fp_name';

            data.forEach(item => {
                pSelect.innerHTML += `<option value="${item.id}">${item[key]}</option>`;
            });
        }
        updateDropdown(); // Initialize
    </script>
</body>
</html>