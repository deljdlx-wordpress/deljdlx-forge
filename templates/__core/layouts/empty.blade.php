<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>
        @yield('page-title', 'PAGE TITLE')
    </title>

    @wp_head
</head>

<body class="@yield('body-css-class')">

    @yield('body-content')


    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
    @wp_footer

</body>
</html>
