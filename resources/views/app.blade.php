<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'BlogVerse Pro') }}</title>
    <meta name="description" content="BlogVerse Pro — platform blog untuk membaca dan menerbitkan artikel seputar teknologi, bisnis, gaya hidup, pendidikan, dan lainnya.">
    <meta name="theme-color" content="#4f46e5">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="bg-white text-slate-800 antialiased">
    @inertia
</body>
</html>
