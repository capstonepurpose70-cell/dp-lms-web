<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-gray-100 flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span class="text-white text-2xl font-bold">DP</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Verify Your Identity</h1>
            <p class="text-sm text-gray-500 mt-1">Two-factor authentication</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-600">
                    A 6-digit code was sent to<br>
                    <span class="font-semibold text-gray-800">{{ session('otp_email') }}</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Expires in <span id="countdown" class="font-semibold text-blue-600">5:00</span>
                </p>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('otp.verify') }}">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-center">
                        Enter verification code
                    </label>
                    <input type="text" name="otp" maxlength="6" required
                        placeholder="• • • • • •"
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3
                               text-center text-2xl font-bold tracking-widest
                               focus:outline-none focus:border-blue-500 transition
                               @error('otp') border-red-400 @enderror">
                    @error('otp')
                        <p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium
                           rounded-lg py-2.5 text-sm transition duration-150">
                    Verify Code
                </button>
            </form>

            <form method="POST" action="{{ route('otp.resend') }}" class="mt-3">
                @csrf
                <button type="submit"
                    class="w-full text-sm text-gray-500 hover:text-blue-600 py-2 transition">
                    Didn't receive a code? Resend
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-xs text-gray-400 hover:text-gray-600">
                    Back to login
                </a>
            </div>
        </div>
    </div>

    <script>
        let seconds = 300;
        const el = document.getElementById('countdown');
        const timer = setInterval(() => {
            seconds--;
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
            if (seconds <= 0) {
                clearInterval(timer);
                el.textContent = 'Expired';
                el.classList.replace('text-blue-600', 'text-red-500');
            }
        }, 1000);
    </script>

</body>
</html>