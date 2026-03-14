<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PageSpeed') — Page Speed Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-gray-50 text-gray-900 antialiased">
    <div class="min-h-full">
        {{-- Navigation --}}
        <nav class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-14 items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('page-speed.index') }}" class="flex items-center gap-2 text-lg font-semibold">
                            <svg class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                            Page Speed
                        </a>
                    </div>
                    <div class="text-sm text-gray-500">
                        <a href="/" class="hover:text-gray-700">&larr; Back to Application</a>
                    </div>
                </div>
            </div>
        </nav>

        {{-- Content --}}
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </div>
</body>
</html>
