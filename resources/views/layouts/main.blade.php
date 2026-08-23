<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    @include('layouts.partials.browser-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">

@include('partials.navbar')

<main class="p-6">
    @yield('content')
</main>

</body>
</html>
