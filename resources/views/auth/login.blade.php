<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <h2>Iniciar sesión</h2>

        <!-- Aviso de inactividad -->
        @if(request('expired') == 1)
            <div class="alert alert-warning">
                No se detectó actividad durante {{ request('mins', config('session.lifetime')) }} minuto(s).
                Por seguridad, tu sesión se cerró.
            </div>
        @endif

        <!-- Mensajes de error del backend -->
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" autocomplete="off">
            @csrf
            <input type="email" name="email" id="email" placeholder="Correo" title="Ingrese su correo" autocomplete="new-email" required>
            <input type="password" name="password" id="password" placeholder="Contraseña" title="Ingrese su contraseña" autocomplete="new-password" required>
            <button type="submit">Iniciar sesión</button>
        </form>
    </div>
</body>
</html>
