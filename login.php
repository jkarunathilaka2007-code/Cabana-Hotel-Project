<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Login | Nature Nest Sri Lanka</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@200;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { 
            font-family: 'Montserrat', sans-serif; 
            background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)), url('img/hero.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .serif { font-family: 'Cormorant Garamond', serif; }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .input-group {
            position: relative;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
        }
        .input-group:focus-within {
            border-bottom-color: #c5a059;
        }

        input {
            background: transparent !important;
            border: none !important;
            outline: none !important;
            color: white !important;
            width: 100%;
            padding: 12px 0;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: rgba(255, 255, 255, 0.4);
            font-weight: 600;
        }

        .swal2-popup {
            background: #1a2e1a !important;
            color: white !important;
            border-radius: 0 !important;
            border: 1px solid #c5a059 !important;
        }
    </style>
</head>
<body class="p-6">

    <a href="index.php" class="absolute top-10 left-10 text-white/50 hover:text-[#c5a059] transition-all flex items-center gap-3 text-[10px] tracking-[0.3em] uppercase">
        <i class="fas fa-long-arrow-alt-left"></i> The Estate
    </a>

    <div class="glass-card w-full max-w-md p-10 md:p-14 rounded-sm relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#c5a059]/10 rounded-full blur-3xl"></div>
        
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl serif italic text-white mb-3">Sign In</h1>
            <p class="text-[9px] uppercase tracking-[0.5em] text-[#c5a059] font-semibold">Welcome Back to the Wild</p>
        </div>

        <form action="login_process.php" method="POST" class="space-y-10">
            <div class="input-group">
                <label><i class="far fa-envelope mr-2"></i> Email Address</label>
                <input type="email" name="email" required placeholder="your@email.com">
            </div>

            <div class="input-group">
                <label><i class="fas fa-lock mr-2"></i> Secret Key</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-[#c5a059] text-white py-4 font-bold uppercase text-[10px] tracking-[0.5em] hover:bg-white hover:text-black transition-all duration-700 shadow-2xl">
                    Open Sanctuary
                </button>
            </div>
        </form>

        <div class="mt-12 text-center">
            <p class="text-[9px] tracking-widest uppercase text-white/30 mb-4">New Guest?</p>
            <a href="register.php" class="text-white text-xs border-b border-[#c5a059]/50 pb-1 hover:text-[#c5a059] hover:border-[#c5a059] transition-all duration-300">Register Your Stay</a>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        
        // 1. Invalid Credentials Error
        if (urlParams.get('error') === 'invalid_credentials') {
            Swal.fire({
                icon: 'error',
                title: 'ACCESS DENIED',
                text: 'The email or password you entered does not match our records.',
                confirmButtonText: 'TRY AGAIN',
                confirmButtonColor: '#c5a059'
            });
        }

        // 2. Logged Out Status
        if (urlParams.has('logged_out')) {
            Swal.fire({
                icon: 'success',
                title: 'NAMASTE',
                text: 'You have been safely disconnected from the nest.',
                showConfirmButton: false,
                timer: 2500
            });
        }
        
        // 3. Unauthorized access (If user tried to access dashboard directly)
        if (urlParams.get('error') === 'unauthorized') {
            Swal.fire({
                icon: 'warning',
                title: 'AUTH REQUIRED',
                text: 'Please login to access this area.',
                confirmButtonColor: '#c5a059'
            });
        }
    </script>
</body>
</html>