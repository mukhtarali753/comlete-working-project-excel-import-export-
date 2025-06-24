<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900">

    <!-- Container to center the content -->
    <div class="relative flex items-center justify-center min-h-screen">

        <!-- Form Container -->
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
            <!-- Form Title -->
            <h2 class="text-center text-2xl font-semibold text-gray-800">Welcome to Theme</h2>

            <!-- Auth Links Form -->
            @if (Route::has('login'))
                <div class="mt-6">
                    @auth
                        <!-- Dashboard Link for Authenticated Users -->
                        <div class="text-center">
                            <a href="{{ url('/dashboard') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Go To Theme Dashboard</a>
                        </div>
                    @else
                        <!-- Login Form --> 
                        <form action="{{ route('login') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-600">Email</label>
                                <input type="email" name="email" id="email" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md" required placeholder="Enter your email">
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-600">Password</label>
                                <input type="password" name="password" id="password" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md" required placeholder="Enter your password">
                            </div>
                            <button type="submit" class="w-full mt-4 py-2 px-4 bg-blue-500 text-white rounded-md">Log In</button>
                        </form>

                        <!-- Register Link -->
                        @if (Route::has('register'))
                            <div class="mt-4 text-center">
                                <a href="{{ route('register') }}" class="text-sm text-blue-600 hover:underline">Don't have an account? Register</a>
                                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">Forgot password</a>

                            </div>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </div>

</body>
</html>
