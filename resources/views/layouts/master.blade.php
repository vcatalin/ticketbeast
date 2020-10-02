<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'TicketBeast')</title>
        @include('scripts.app')
    </head>
    <body class="bg-dark">
        <div class="app">
            @yield('body')
        </div>

        @stack('beforeScripts')
        <script src="{{ elixir('js/app.js') }}"></script>
        @stack('afterScripts')
    </body>
</html>
