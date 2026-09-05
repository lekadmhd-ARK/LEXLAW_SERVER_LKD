<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEXLAW v2 - Legal SaaS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/dashboard" class="text-xl font-bold text-indigo-600">LEXLAW v2</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/billing" class="text-sm text-gray-700 hover:text-indigo-600">Billing</a>
                    <a href="/super-admin/plans" class="text-sm text-gray-700 hover:text-indigo-600">Plans</a>
                    <a href="/admin" class="text-sm text-gray-700 hover:text-indigo-600">Admin</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm text-gray-700 hover:text-indigo-600">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield("content")
    </main>
</body>
</html>
