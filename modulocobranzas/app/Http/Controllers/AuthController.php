<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function ShowLoginForm()
{
    return response()
        ->view('auth.login')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
}

    public function login(Request $request) {
        // Obtener las credenciales
        $credentials = $request->only('email', 'password');

        // Intentar autenticar al usuario
        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            // Verificar si el usuario existe en la base de datos
            if ($user) {
                // Si el usuario existe, redirigir al dashboard
                return redirect()->intended('dashboard');
            } else {
                // Si el usuario no existe, cerrar sesión y mostrar un mensaje
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'El usuario no está registrado en la base de datos.',
                ]);
            }
        }
        // Si las credenciales no son correctas
        return back()->withErrors([
            'email' => 'Las credenciales introducidas son incorrectas o no existen en los registros.',
        ]);
    }

    public function logout(Request $request)
{
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Limpiar los valores del formulario
    return redirect()->route('login')->withInput(['email' => '', 'password' => '']);
}

}
