<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

// 1. දැනට බුක් වී ඇති දින ලබා ගැනීම (Array එකක් ලෙස)
$booked_dates = [];
$res = $conn->query("SELECT check_in, check_out FROM bookings WHERE status != 'cancelled'");
while($row = $res->fetch_assoc()) {
    $begin = new DateTime($row['check_in']);
    $end = new DateTime($row['check_out']);
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($begin, $interval, $end);
    foreach ($period as $dt) {
        $booked_dates[] = $dt->format("Y-m-d");
    }
}

// 2. Offers තියෙන දින පරාසයන් ලබා ගැනීම
$offer_ranges = [];
$offers = $conn->query("SELECT valid_from, valid_to, discount_percentage FROM offers WHERE status = 'active'");
while($off = $offers->fetch_assoc()) {
    $offer_ranges[] = $off;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Dates | Nature Nest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Cormorant+Garamond:ital,wght@1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #050805; color: white; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        .glass-card { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(197, 160, 89, 0.1); }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; }
        .day-btn { aspect-ratio: 1/1; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: 0.3s; font-size: 13px; cursor: pointer; position: relative; border-radius: 4px; border: 1px solid rgba(255,255,255,0.03); }
        .day-btn.past { opacity: 0.1; cursor: not-allowed; pointer-events: none; }
        .day-btn.booked { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); cursor: not-allowed; pointer-events: none; }
        .day-btn.booked::after { content: 'Sold'; position: absolute; bottom: 2px; font-size: 7px; text-transform: uppercase; opacity: 0.6; }
        .day-btn.has-offer { border-color: rgba(197, 160, 89, 0.4); }
        .day-btn.has-offer::before { content: ''; position: absolute; top: 4px; right: 4px; width: 4px; height: 4px; background: #c5a059; border-radius: 50%; box-shadow: 0 0 5px #c5a059; }
        .day-btn.selected { background: #c5a059 !important; color: #000 !important; font-weight: bold; border-color: #c5a059; }
        .day-btn.in-range { background: rgba(197, 160, 89, 0.2); color: #c5a059; border-color: rgba(197, 160, 89, 0.3); }
        .day-name { text-align: center; font-size: 10px; opacity: 0.3; padding-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body class="p-4 md:p-10">

    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-end mb-12">
            <div>
                <h1 class="serif text-4xl italic text-[#c5a059]">Arrival & Departure</h1>
                <p id="dateStatus" class="text-[9px] uppercase tracking-[0.4em] mt-2 opacity-50">Select your check-in date to begin</p>
            </div>
            <div class="hidden md:flex gap-6">
                <div class="flex items-center gap-2 text-[8px] uppercase tracking-widest opacity-40">
                    <span class="w-2 h-2 bg-red-500/20 border border-red-500/40 block"></span> Booked
                </div>
                <div class="flex items-center gap-2 text-[8px] uppercase tracking-widest text-[#c5a059]">
                    <span class="w-2 h-2 rounded-full bg-[#c5a059] block"></span> Offer
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 glass-card p-8 rounded-sm">
                <div class="flex justify-between items-center mb-10">
                    <button onclick="prevMonth()" id="prevBtn" class="text-[#c5a059] p-2"><i class="fas fa-chevron-left"></i></button>
                    <h2 id="monthDisplay" class="serif text-2xl italic tracking-[0.2em] uppercase">Month Year</h2>
                    <button onclick="nextMonth()" class="text-[#c5a059] p-2"><i class="fas fa-chevron-right"></i></button>
                </div>

                <div class="calendar-grid mb-2">
                    <div class="day-name">Sun</div><div class="day-name">Mon</div><div class="day-name">Tue</div>
                    <div class="day-name">Wed</div><div class="day-name">Thu</div><div class="day-name">Fri</div>
                    <div class="day-name">Sat</div>
                </div>
                <div id="daysGrid" class="calendar-grid"></div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="glass-card p-8 flex-1">
                    <h3 class="serif text-xl italic mb-8 border-b border-white/5 pb-4">Stay Summary</h3>
                    <div class="space-y-6">
                        <div class="flex justify-between">
                            <span class="text-[10px] uppercase opacity-40">Arrival</span>
                            <span id="displayCheckIn" class="text-xs font-bold text-[#c5a059]">-- / -- / --</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[10px] uppercase opacity-40">Departure</span>
                            <span id="displayCheckOut" class="text-xs font-bold text-[#c5a059]">-- / -- / --</span>
                        </div>
                        <div class="pt-6 border-t border-white/5 flex justify-between items-baseline">
                            <span class="text-[10px] uppercase opacity-40">Total Nights</span>
                            <span id="displayNights" class="serif text-3xl italic">0</span>
                        </div>
                    </div>

                    <form action="book_rooms2.php" method="POST" class="mt-12">
                        <input type="hidden" name="check_in" id="checkInInput">
                        <input type="hidden" name="check_out" id="checkOutInput">
                        <button type="submit" id="nextBtn" disabled class="w-full py-4 bg-[#c5a059] text-black font-bold uppercase text-[10px] tracking-[0.3em] opacity-20 cursor-not-allowed transition hover:bg-white shadow-xl">
                            Next: Select Room
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let checkIn = null;
        let checkOut = null;
        const today = new Date(); today.setHours(0,0,0,0);
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();

        const bookedDates = <?php echo json_encode($booked_dates); ?>;
        const offerRanges = <?php echo json_encode($offer_ranges); ?>;

        function renderCalendar() {
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            document.getElementById('monthDisplay').innerText = new Date(currentYear, currentMonth).toLocaleString('default', { month: 'long', year: 'numeric' });
            
            const grid = document.getElementById('daysGrid');
            grid.innerHTML = '';

            for (let i = 0; i < firstDay; i++) { grid.innerHTML += '<div></div>'; }

            for (let day = 1; day <= daysInMonth; day++) {
                let date = new Date(currentYear, currentMonth, day);
                let dateStr = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                
                let isPast = date < today;
                let isBooked = bookedDates.includes(dateStr);
                let hasOffer = offerRanges.some(off => dateStr >= off.valid_from && dateStr <= off.valid_to);
                
                let classes = 'day-btn';
                if (isPast) classes += ' past';
                if (isBooked) classes += ' booked';
                if (hasOffer && !isBooked && !isPast) classes += ' has-offer';
                
                if (checkIn && dateStr === checkIn) classes += ' selected';
                if (checkOut && dateStr === checkOut) classes += ' selected';
                if (checkIn && checkOut && date > new Date(checkIn) && date < new Date(checkOut)) classes += ' in-range';

                grid.innerHTML += `<div class="${classes}" onclick="handleDateClick('${dateStr}', ${isBooked})">${day}</div>`;
            }
        }

        function handleDateClick(dateStr, isBooked) {
            if (isBooked) return;

            if (!checkIn || (checkIn && checkOut)) {
                checkIn = dateStr;
                checkOut = null;
                document.getElementById('dateStatus').innerHTML = "Now select your departure date";
            } else {
                if (new Date(dateStr) <= new Date(checkIn)) {
                    checkIn = dateStr;
                    checkOut = null;
                } else {
                    // Overlap Validation: Check if any date in the selected range is already booked
                    let tempIn = new Date(checkIn);
                    let tempOut = new Date(dateStr);
                    let isInvalidRange = false;

                    for (let d = new Date(tempIn); d < tempOut; d.setDate(d.getDate() + 1)) {
                        let dStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                        if (bookedDates.includes(dStr)) {
                            isInvalidRange = true;
                            break;
                        }
                    }

                    if (isInvalidRange) {
                        alert("Error: Selected range contains already booked dates! Please choose a clear period.");
                        checkIn = null;
                        checkOut = null;
                        document.getElementById('dateStatus').innerHTML = "<span class='text-red-500'>Range blocked by existing booking. Select Check-in again.</span>";
                    } else {
                        checkOut = dateStr;
                        document.getElementById('dateStatus').innerHTML = "Perfect! Continue to selection.";
                    }
                }
            }
            updateUI();
            renderCalendar();
        }

        function updateUI() {
            document.getElementById('displayCheckIn').innerText = checkIn || "-- / -- / --";
            document.getElementById('displayCheckOut').innerText = checkOut || "-- / -- / --";
            document.getElementById('checkInInput').value = checkIn;
            document.getElementById('checkOutInput').value = checkOut;

            if (checkIn && checkOut) {
                let diff = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24));
                document.getElementById('displayNights').innerText = diff;
                document.getElementById('nextBtn').disabled = false;
                document.getElementById('nextBtn').classList.remove('opacity-20', 'cursor-not-allowed');
            } else {
                document.getElementById('displayNights').innerText = "0";
                document.getElementById('nextBtn').disabled = true;
                document.getElementById('nextBtn').classList.add('opacity-20', 'cursor-not-allowed');
            }
        }

        function nextMonth() { currentMonth++; if(currentMonth>11){currentMonth=0; currentYear++;} renderCalendar(); }
        function prevMonth() { if(currentYear > today.getFullYear() || currentMonth > today.getMonth()){ currentMonth--; if(currentMonth<0){currentMonth=11; currentYear--;} renderCalendar(); }}

        renderCalendar();
    </script>
</body>
</html>