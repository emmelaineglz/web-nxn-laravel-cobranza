<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bienvenido al Sistema</title>

    <!-- Vincula una hoja de estilos externa o agrega un bloque de estilos dentro de la página -->
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
</head>
<body>

    <div class="welcome-container">
        <h1>¡Bienvenido al sistema!</h1>
        <p>Estamos encantados de tenerte aquí. </p>

        <!-- Enlace de login como un botón -->
        @if (Route::has('login'))
            <div class="login-nav">
                <a href="{{ route('login') }}">Iniciar sesión</a>
            </div>
        @endif
    </div>

</body>
</html>
