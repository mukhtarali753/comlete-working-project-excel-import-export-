<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .marquee {
            overflow: hidden;
            white-space: nowrap;
        }
        .marquee span {
            display: inline-block;
            animation: marquee 12s linear infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 via-white to-blue-200 min-h-screen flex items-center justify-center">

    <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-md text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome to Theme</h2>
        {{-- <p class="text-gray-500 mb-6">Your personalized dashboard awaits</p> --}}

        @if (Route::has('login'))
            <div>
                @auth
                    <!-- Logged-in View -->
                    <div class="bg-green-100 text-green-700 py-2 px-4 rounded-md font-semibold mb-6">
                        Logged in as: <span class="text-green-900">{{ Auth::user()->name }}</span>
                    </div>

                    <!-- Marquee Link -->
                   <a href="{{ url('/dashboard') }}" class="block marquee bg-blue-600 text-white py-2 rounded-md shadow-md hover:bg-blue-700 transition duration-300">
    <span class="font-medium text-lg">
        👉 Welcome {{ Auth::user()->name }}! Click here to Go to Theme Dashboard 👈
    </span>
</a>

                @else
                    <!-- Login Form -->
                    <form action="{{ route('login') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-600 text-left">Email</label>
                            <input type="email" name="email" id="email" 
                                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-400 focus:border-blue-400" 
                                   required placeholder="Enter your email">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-600 text-left">Password</label>
                            <input type="password" name="password" id="password" 
                                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-400 focus:border-blue-400" 
                                   required placeholder="Enter your password">
                        </div>
                        <button type="submit" class="w-full py-2 mt-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition duration-200">
                            Log In
                        </button>
                    </form>

                    <!-- Register + Forgot Links -->
                    @if (Route::has('register'))
                        <div class="mt-6 space-y-2">
                            <a href="{{ route('register') }}" class="block text-sm text-blue-600 hover:underline">
                                Don’t have an account? Register
                            </a>
                            <a href="{{ route('password.request') }}" class="block text-sm text-blue-600 hover:underline">
                                Forgot password?
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
        @endif
    </div>

</body>
</html>
