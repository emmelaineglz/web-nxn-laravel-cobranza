<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="container">
        @php
            $user = Auth::user();
        @endphp

        <h1>
            @if($user->role === 'admin')
                Hola, Admin
            @elseif($user->role === 'user')
                Hola, Usuario
            @else
                Hola, Invitado
            @endif
        </h1>

        @if($user->role === 'admin')
            <p class="fecha">Fecha actual: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        @endif

        <form method="POST" action="{{ route('logout') }}" style="margin-top: 30px;">
            @csrf
            <button type="submit" class="logout-button">Cerrar sesión</button>
        </form>
    </div>
</body>
</html>
