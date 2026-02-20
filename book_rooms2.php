<?php
session_start();
require_once 'db_config.php';

if (!isset($_POST['check_in'])) { header("Location: book_rooms.php"); exit(); }

$check_in = $_POST['check_in'];
$check_out = $_POST['check_out'];
$nights = (new DateTime($check_out))->diff(new DateTime($check_in))->days;

// 1. Fetch All Packages (අපි මේවා JS එකෙන් Filter කරනවා)
$rooms = $conn->query("SELECT * FROM packages");

// 2. Food Packages fetching
$food_res = $conn->query("SELECT fp.*, GROUP_CONCAT(fi.item_name SEPARATOR '|') as item_names, GROUP_CONCAT(fi.item_image SEPARATOR '|') as item_images FROM food_packages fp LEFT JOIN food_package_items fi ON fp.id = fi.food_package_id GROUP BY fp.id");
$all_foods = []; while($f = $food_res->fetch_assoc()) { $all_foods[] = $f; }

// 3. Activities fetching
$activities = $conn->query("SELECT * FROM activities");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nature Nest | Bespoke Stay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Playfair+Display:ital,wght@1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #050805; color: white; scroll-behavior: smooth; overflow-x: hidden; }
        .playfair { font-family: 'Playfair Display', serif; }
        .glass { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(197, 160, 89, 0.1); }
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeIn 0.8s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        .selected { border-color: #c5a059 !important; background: rgba(197, 160, 89, 0.1) !important; box-shadow: 0 0 15px rgba(197, 160, 89, 0.1); }
        #menuModal { backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); background: rgba(0,0,0,0.85); transition: all 0.4s ease; }
        .switch { position: relative; display: inline-block; width: 46px; height: 22px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #c5a059; }
        input:checked + .slider:before { transform: translateX(24px); }
        input[type="text"], input[type="time"] { background: rgba(255,255,255,0.03); border: 1px solid rgba(197, 160, 89, 0.2); color: white; padding: 12px; border-radius: 4px; font-size: 13px; width: 100%; outline: none; transition: 0.3s; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="p-4 md:p-8 lg:p-12">

<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
    
    <div class="lg:col-span-8 order-2 lg:order-1">
        <header class="mb-10 text-center lg:text-left">
            <h1 class="playfair text-4xl md:text-5xl italic text-[#c5a059]">Refine Your Journey</h1>
            <p class="text-[10px] uppercase tracking-[0.5em] opacity-40 mt-3"><?= $nights ?> Nights | Sanctuary Stay</p>
        </header>

        <div id="step1" class="step-content active space-y-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div onclick="setResidency('local')" id="resLocal" class="glass p-10 text-center cursor-pointer hover:bg-white/5 transition border border-white/5 group rounded-sm">
                    <i class="fas fa-home mb-3 opacity-20 group-hover:text-[#c5a059] group-hover:opacity-100 transition"></i>
                    <p class="text-[11px] font-bold tracking-widest uppercase italic">Sri Lankan Guest</p>
                </div>
                <div onclick="setResidency('foreign')" id="resForeign" class="glass p-10 text-center cursor-pointer hover:bg-white/5 transition border border-white/5 group rounded-sm">
                    <i class="fas fa-globe mb-3 opacity-20 group-hover:text-[#c5a059] group-hover:opacity-100 transition"></i>
                    <p class="text-[11px] font-bold tracking-widest uppercase italic">International Guest</p>
                </div>
            </div>
            
            <div id="guestSection" class="hidden text-center lg:text-left">
                <h3 class="playfair text-2xl italic mb-6">How many travelers?</h3>
                <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                    <?php for($i=1; $i<=6; $i++): ?>
                    <button onclick="setGuests(<?= $i ?>)" class="guest-btn w-12 h-12 rounded-full border border-white/10 hover:border-[#c5a059] transition text-sm"><?= $i ?></button>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <div id="step2" class="step-content space-y-8">
            <h3 class="playfair text-3xl italic">Select Your Sanctuary</h3>
            <div id="roomContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php while($r = $rooms->fetch_assoc()): ?>
                <div class="glass room-card overflow-hidden cursor-pointer group rounded-sm" 
                     data-id="<?= $r['id'] ?>" 
                     data-name="<?= $r['package_name'] ?>"
                     data-lkr="<?= $r['price_lkr'] ?>" 
                     data-usd="<?= $r['price_usd'] ?>"
                     data-cap="<?= $r['max_guests'] ?>" 
                     onclick="selectRoom(this)">
                    <div class="h-44 overflow-hidden"><img src="<?= $r['cover_image'] ?>" class="w-full h-full object-cover opacity-40 group-hover:opacity-100 transition duration-1000"></div>
                    <div class="p-5 flex justify-between items-center bg-black/40 border-t border-white/5">
                        <div>
                            <span class="playfair text-lg italic block"><?= $r['package_name'] ?></span>
                            <span class="text-[8px] uppercase tracking-widest opacity-40">Max Guests: <?= $r['max_guests'] ?></span>
                        </div>
                        <span class="text-[#c5a059] font-bold price-tag text-xs"></span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div id="noRoomsMsg" class="hidden glass p-10 text-center opacity-50 italic">No packages available for the selected number of guests.</div>
            
            <div class="flex gap-4 pt-6">
                <button onclick="prevStep(1)" class="w-1/3 py-4 border border-white/10 text-[9px] uppercase tracking-widest opacity-40 hover:opacity-100">Back</button>
                <button onclick="nextStep(3)" id="btnToStep3" disabled class="w-2/3 py-4 bg-white text-black text-[10px] font-bold uppercase tracking-[0.4em] opacity-20 transition-all">Select Dining Plans</button>
            </div>
        </div>

        <div id="step3" class="step-content space-y-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 border-b border-white/5 pb-4">
                <h3 class="playfair text-3xl italic text-[#c5a059]">Gastronomy (Per Person)</h3>
                <div class="flex p-1 bg-white/5 rounded-sm">
                    <button id="scopeSame" onclick="setScope('same')" class="px-5 py-2 text-[9px] uppercase font-bold tracking-tighter transition rounded-sm">Same Daily</button>
                    <button id="scopeDiff" onclick="setScope('diff')" class="px-5 py-2 text-[9px] uppercase font-bold tracking-tighter transition rounded-sm opacity-30">Customize Daily</button>
                </div>
            </div>
            <div id="dayTabs" class="hidden flex gap-2 overflow-x-auto pb-2 no-scrollbar"></div>
            <div id="mealSelectionArea" class="space-y-10"></div>
            <div class="flex gap-4 mt-8">
                <button onclick="prevStep(2)" class="w-1/3 py-4 border border-white/10 text-[9px] uppercase tracking-widest opacity-40 hover:opacity-100">Back</button>
                <button onclick="nextStep(4)" class="w-2/3 py-4 bg-white text-black text-[10px] font-bold uppercase tracking-[0.4em]">Finalize Experiences</button>
            </div>
        </div>

        <div id="step4" class="step-content space-y-10">
            <div>
                <h3 class="playfair text-3xl italic mb-6">Extra Experiences</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php while($a = $activities->fetch_assoc()): ?>
                    <div class="glass p-4 flex gap-4 items-center cursor-pointer act-card rounded-sm group transition-all" 
                         data-id="<?= $a['id'] ?>" data-name="<?= $a['activity_name'] ?>"
                         data-lkr="<?= $a['price_lkr'] ?>" data-usd="<?= $a['price_usd'] ?>" onclick="toggleAct(this)">
                        <img src="<?= $a['activity_image'] ?>" class="w-14 h-14 object-cover rounded-sm grayscale group-hover:grayscale-0 transition-all">
                        <div class="flex-1"><p class="playfair italic text-md"><?= $a['activity_name'] ?></p><p class="text-[#c5a059] text-[10px] price-tag"></p></div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="glass p-6 md:p-10 border-l-4 border-[#c5a059] bg-[#c5a059]/5">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h4 class="playfair text-2xl italic">Luxury Transport</h4>
                        <p class="text-[9px] uppercase tracking-widest opacity-50 mt-1">Pickup and Drop-off Services</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="transportToggle" onchange="toggleTransportFields()">
                        <span class="slider"></span>
                    </label>
                </div>

                <div id="transportFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-white/5">
                    <div class="space-y-4">
                        <p class="text-[10px] uppercase font-bold text-[#c5a059] tracking-widest"><i class="fas fa-plane-arrival mr-2"></i> Arrival Details</p>
                        <input type="text" id="pickup_loc" placeholder="Pickup Location">
                        <input type="time" id="pickup_time">
                    </div>
                    <div class="space-y-4">
                        <p class="text-[10px] uppercase font-bold text-[#c5a059] tracking-widest"><i class="fas fa-plane-departure mr-2"></i> Departure Details</p>
                        <input type="text" id="drop_loc" placeholder="Drop Location">
                        <input type="time" id="drop_time">
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button onclick="prevStep(3)" class="w-1/3 py-5 border border-white/10 text-[9px] uppercase tracking-widest opacity-40 hover:opacity-100">Back</button>
                <button onclick="submitBooking()" id="submitBtn" class="w-2/3 py-5 bg-[#c5a059] text-black text-[11px] font-bold uppercase tracking-[0.5em] hover:bg-white transition duration-500 shadow-xl">Confirm Sanctuary Selection</button>
            </div>
        </div>
    </div>

    <div class="lg:col-span-4 order-1 lg:order-2">
        <div class="glass p-8 sticky top-10 border-t-4 lg:border-t-0 lg:border-l-2 border-[#c5a059] shadow-2xl rounded-sm">
            <h3 class="playfair text-2xl italic mb-8 text-[#c5a059] flex items-center gap-3">
                <i class="fas fa-file-invoice-dollar text-sm opacity-50"></i> Reservation
            </h3>
            <div id="billItems" class="space-y-5 text-[10px] uppercase tracking-widest leading-relaxed">
                <p class="opacity-30 italic py-10 text-center">Select residency to begin...</p>
            </div>
            <div class="mt-12 pt-8 border-t border-white/10">
                <p class="text-[9px] opacity-40 uppercase tracking-[0.4em] mb-2">Estimated Investment</p>
                <div class="flex justify-between items-baseline">
                    <span class="playfair text-4xl text-[#c5a059] italic" id="totalVal">0.00</span>
                    <span id="currLabel" class="text-[11px] font-bold opacity-60 uppercase">---</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="menuModal" class="fixed inset-0 hidden z-[5000] flex items-center justify-center p-4">
    <div class="glass max-w-4xl w-full max-h-[85vh] overflow-y-auto p-8 md:p-14 relative border-[#c5a059]/30 shadow-2xl">
        <button onclick="closeMenu()" class="absolute top-6 right-8 text-4xl opacity-20 hover:opacity-100 hover:text-[#c5a059] transition-all">&times;</button>
        <div class="text-center mb-10">
            <h3 id="modalTitle" class="playfair text-4xl italic text-[#c5a059]"></h3>
            <div class="w-24 h-px bg-[#c5a059]/30 mx-auto mt-6"></div>
        </div>
        <div id="modalGrid" class="grid grid-cols-1 md:grid-cols-2 gap-8"></div>
    </div>
</div>

<script>
    const allFoods = <?= json_encode($all_foods) ?>;
    const nights = <?= $nights ?>;
    const mealTypes = [
        {id:'bed_tea', name:'Bed Tea'}, {id:'breakfast', name:'Breakfast'}, 
        {id:'lunch', name:'Lunch'}, {id:'evening_tea', name:'Evening Tea'}, {id:'dinner', name:'Dinner'}
    ];
    
    let booking = { residency:'', currency:'', guests:0, room:null, scope:'same', activeDay:1, selections:{}, acts:[], transport:false };

    for(let i=1; i<=nights; i++) booking.selections[i] = { bed_tea:null, breakfast:null, lunch:null, evening_tea:null, dinner:null };

    function setResidency(r) {
        booking.residency = r; booking.currency = r==='local'?'LKR':'USD';
        document.querySelectorAll('#resLocal, #resForeign').forEach(el=>el.classList.remove('selected'));
        document.getElementById('res'+(r==='local'?'Local':'Foreign')).classList.add('selected');
        document.getElementById('guestSection').classList.remove('hidden');
        document.getElementById('currLabel').innerText = booking.currency;
        refreshPrices();
    }

    function setGuests(g) {
        booking.guests = g;
        document.querySelectorAll('.guest-btn').forEach(b=>b.classList.remove('selected'));
        event.target.classList.add('selected');

        // Logic: Filter packages based on capacity
        let availableCount = 0;
        document.querySelectorAll('.room-card').forEach(card => {
            const maxCap = parseInt(card.dataset.cap);
            if(maxCap >= g) {
                card.style.display = 'block';
                availableCount++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('noRoomsMsg').classList.toggle('hidden', availableCount > 0);
        
        // Auto move to room selection
        setTimeout(()=>nextStep(2), 600);
    }

    function refreshPrices() {
        const k = booking.currency.toLowerCase();
        document.querySelectorAll('.price-tag').forEach(p => {
            const parent = p.closest('[data-lkr]');
            if(parent) {
                const price = parseFloat(parent.getAttribute('data-'+k));
                p.innerText = booking.currency + " " + price.toLocaleString();
                parent.setAttribute('data-current', price);
            }
        });
        updateBill();
    }

    function selectRoom(el) {
        booking.room = { id: el.dataset.id, name: el.dataset.name, price: parseFloat(el.dataset.current) };
        document.querySelectorAll('.room-card').forEach(c=>c.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('btnToStep3').disabled = false;
        document.getElementById('btnToStep3').classList.remove('opacity-20');
        updateBill();
    }

    function setScope(s) {
        booking.scope = s;
        document.getElementById('scopeSame').classList.toggle('opacity-30', s!=='same');
        document.getElementById('scopeDiff').classList.toggle('opacity-30', s!=='diff');
        
        const tabArea = document.getElementById('dayTabs');
        if(s==='diff') {
            tabArea.classList.remove('hidden');
            let tabs = '';
            for(let i=1; i<=nights; i++) tabs += `<button onclick="setDay(${i})" class="day-tab flex-none px-6 py-2 glass text-[9px] ${booking.activeDay===i?'selected':''} uppercase tracking-widest rounded-sm">Day ${i}</button>`;
            tabArea.innerHTML = tabs;
        } else { tabArea.classList.add('hidden'); }
        renderMeals();
    }

    function setDay(d) { booking.activeDay = d; setScope('diff'); }

    function renderMeals() {
        let html = `<p class="text-[9px] italic text-[#c5a059] uppercase tracking-[0.4em] mb-4">${booking.scope==='same'?'Stay Duration Config':'Day '+booking.activeDay+' Config'}</p>`;
        mealTypes.forEach(mt => {
            html += `<div class="space-y-4">
                <p class="text-[8px] uppercase tracking-[0.4em] opacity-40 font-bold border-l-2 border-[#c5a059] pl-3">${mt.name}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    ${allFoods.map(f => {
                        const isSel = booking.selections[booking.activeDay][mt.id]?.id == f.id;
                        const price = parseFloat(f['price_'+booking.currency.toLowerCase()]);
                        return `<div class="glass p-4 cursor-pointer group transition rounded-sm relative ${isSel?'selected':''}" onclick="chooseMeal('${mt.id}', ${f.id})">
                            <p class="playfair text-sm italic mb-1">${f.fp_name}</p>
                            <p class="text-[#c5a059] text-[10px] font-bold">${booking.currency} ${price.toLocaleString()}</p>
                            <button onclick="openMenu(event, ${f.id})" class="mt-3 text-[8px] uppercase tracking-tighter opacity-30 hover:opacity-100 hover:text-[#c5a059] flex items-center gap-1"><i class="fas fa-eye text-[7px]"></i> View Menu</button>
                        </div>`;
                    }).join('')}
                </div>
            </div>`;
        });
        document.getElementById('mealSelectionArea').innerHTML = html;
        updateBill();
    }

    function chooseMeal(type, id) {
        const f = allFoods.find(food => food.id == id);
        const price = parseFloat(f['price_'+booking.currency.toLowerCase()]);
        if(booking.scope === 'same') {
            for(let i=1; i<=nights; i++) booking.selections[i][type] = { id, name: f.fp_name, price };
        } else {
            booking.selections[booking.activeDay][type] = { id, name: f.fp_name, price };
        }
        renderMeals();
    }

    function updateBill() {
        let html = ''; let total = 0;
        if(booking.room) { 
            let p = booking.room.price * nights; total += p;
            html += `<div class="flex justify-between border-b border-white/5 pb-2"><span>${booking.room.name}<br><span class="opacity-40 lowercase">Per Stay (Fixed)</span></span><span>${p.toLocaleString()}</span></div>`;
        }
        let foodTotal = 0;
        for(let d=1; d<=nights; d++) {
            for(let m in booking.selections[d]) {
                if(booking.selections[d][m]) foodTotal += (booking.selections[d][m].price * booking.guests);
            }
        }
        if(foodTotal > 0) {
            total += foodTotal;
            html += `<div class="flex justify-between text-[#c5a059]"><span>Dining Experience<br><span class="opacity-60 lowercase">${booking.guests} Guests × ${nights}N</span></span><span>${foodTotal.toLocaleString()}</span></div>`;
        }
        booking.acts.forEach(a => { total += a.price; html += `<div class="flex justify-between opacity-40"><span>+ ${a.name}</span><span>${a.price.toLocaleString()}</span></div>`; });
        if(booking.transport) html += `<div class="mt-4 pt-4 border-t border-[#c5a059]/20 text-[8px] text-[#c5a059] italic text-center uppercase tracking-widest">Transport Details Added</div>`;

        document.getElementById('billItems').innerHTML = html || '<p class="opacity-10 italic py-10 text-center">Your sanctuary is waiting...</p>';
        document.getElementById('totalVal').innerText = total.toLocaleString(undefined, {minimumFractionDigits:2});
    }

    function toggleTransportFields() {
        booking.transport = document.getElementById('transportToggle').checked;
        document.getElementById('transportFields').classList.toggle('hidden', !booking.transport);
        updateBill();
    }

    function openMenu(e, id) {
        e.stopPropagation();
        const f = allFoods.find(f => f.id == id);
        document.getElementById('modalTitle').innerText = f.fp_name;
        const names = f.item_names.split('|'); const imgs = f.item_images.split('|');
        document.getElementById('modalGrid').innerHTML = names.map((n, i) => `
            <div class="flex gap-5 items-center border-b border-white/5 pb-5 group">
                <div class="w-20 h-20 flex-none overflow-hidden rounded-sm"><img src="${imgs[i]}" class="w-full h-full object-cover"></div>
                <div><p class="playfair italic text-xl text-white group-hover:text-[#c5a059] transition">${n}</p><p class="text-[8px] uppercase tracking-widest text-[#c5a059] opacity-60">Chef's Gourmet Craft</p></div>
            </div>`).join('');
        document.getElementById('menuModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() { document.getElementById('menuModal').classList.add('hidden'); document.body.style.overflow = 'auto'; }
    function nextStep(s) { document.querySelectorAll('.step-content').forEach(c=>c.classList.remove('active')); document.getElementById('step'+s).classList.add('active'); window.scrollTo(0,0); }
    function prevStep(s) { nextStep(s); }
    
    function toggleAct(el) {
        const id = el.dataset.id; const idx = booking.acts.findIndex(a=>a.id===id);
        if(idx>-1) { booking.acts.splice(idx,1); el.classList.remove('selected'); }
        else { booking.acts.push({id, name:el.dataset.name, price:parseFloat(el.dataset.current)}); el.classList.add('selected'); }
        updateBill();
    }

    function submitBooking() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true; btn.innerText = "Processing...";

        const finalData = { 
            check_in: '<?= $check_in ?>',
            check_out: '<?= $check_out ?>',
            nights: nights,
            residency: booking.residency,
            guests: booking.guests,
            room_id: booking.room ? booking.room.id : null,
            total_price: document.getElementById('totalVal').innerText.replace(/,/g, ''),
            currency: booking.currency,
            transport: booking.transport ? {
                pickup_loc: document.getElementById('pickup_loc').value,
                pickup_time: document.getElementById('pickup_time').value,
                drop_loc: document.getElementById('drop_loc').value,
                drop_time: document.getElementById('drop_time').value
            } : null,
            selections: booking.selections,
            activities: booking.acts
        };

        fetch('save_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(finalData)
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) window.location.href = "thank_you.php?id=" + data.booking_id;
            else { alert(data.message); btn.disabled = false; btn.innerText = "Confirm Sanctuary Selection"; }
        });
    }
</script>
</body>
</html>