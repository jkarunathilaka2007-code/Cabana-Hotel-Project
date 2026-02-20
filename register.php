<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Nest | Nature Nest Registration</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@200;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Montserrat', sans-serif; 
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('img/hero.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .serif { font-family: 'Cormorant Garamond', serif; }
        
        .glass-box {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .input-group { position: relative; border-bottom: 1px solid rgba(255, 255, 255, 0.2); transition: 0.4s; }
        .input-group:focus-within { border-bottom-color: #c5a059; }

        input, select {
            background: transparent !important;
            border: none !important;
            outline: none !important;
            color: white !important;
            width: 100%;
            padding: 10px 0;
            font-size: 0.85rem;
        }

        label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
        }

        select option { background: #1a2e1a; color: white; }
    </style>
</head>
<body class="p-6">

    <a href="index.php" class="absolute top-8 left-8 text-white/50 hover:text-[#c5a059] transition-all flex items-center gap-3 text-[10px] tracking-[0.3em] uppercase">
        <i class="fas fa-chevron-left"></i> Return Home
    </a>

    <div class="glass-box w-full max-w-lg p-8 md:p-12 rounded-sm" data-aos="fade-up">
        <div class="text-center mb-12">
            <h2 class="text-4xl serif italic text-white mb-3 tracking-wide">Create Your Account</h2>
            <div class="h-[1px] w-12 bg-[#c5a059] mx-auto opacity-50"></div>
        </div>

        <form action="auth_handler.php" method="POST" class="space-y-8">
            <input type="hidden" name="action" value="register">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="John Doe" required>
                </div>
                <div class="input-group">
                    <label>WhatsApp Number</label>
                    <input type="tel" name="phone" placeholder="+94 7X XXX XXXX" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="example@mail.com" required>
                </div>
                <div class="input-group">
                    <label>Residency Status</label>
                    <select name="nationality" required>
                        <option value="local">Sri Lankan Resident (Local)</option>
                        <option value="foreigner">International Guest (Foreigner)</option>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <label>Secure Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full bg-[#c5a059] text-white py-4 font-bold uppercase text-[10px] tracking-[0.4em] hover:bg-white hover:text-black transition-all duration-500 shadow-2xl">
                    Confirm Registration
                </button>
            </div>
        </form>

        <div class="mt-12 text-center text-white/40 text-[9px] tracking-widest uppercase">
            Already a member? 
            <a href="login.php" class="text-white border-b border-[#c5a059]/40 hover:text-[#c5a059] transition ml-2 pb-1">Login to Nest</a>
        </div>
    </div>

</body>
</html>