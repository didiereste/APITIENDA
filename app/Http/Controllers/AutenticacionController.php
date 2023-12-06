<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Autenticacion
 *
 * Controlador para la autenticación de usuarios.
 */
class AutenticacionController extends Controller
{
    /**
     * Registrar un nuevo usuario.
     *
     * @bodyParam name string required Nombre del usuario.
     * @bodyParam email string required Correo electrónico único del usuario.
     * @bodyParam password string required Contraseña del usuario (mínimo 6 caracteres).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registro(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);

            $user = User::create($request->all());
            // El usuario que se registra de entrada queda con el rol de usuario (asumiendo que 2 es el ID del rol de usuario).
            $user->assignRole(2);

            return ApiResponse::success('Usuario registrado correctamente', 201, $user);
        } catch (ValidationException $e) {
            return ApiResponse::error('Error de validación: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Iniciar sesión de usuario.
     *
     * @bodyParam email string required Correo electrónico del usuario.
     * @bodyParam password string required Contraseña del usuario.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ingreso(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return ApiResponse::error('Credenciales incorrectas', 400);
            }

            // Creamos un token con createToken y lo asignamos al usuario que se logueó en el momento, especificando que queremos el token en texto plano con plainTextToken.
            $token = $user->createToken($request->email)->plainTextToken;

            return ApiResponse::success('Inicio de sesión exitoso', 200, $token);
        } catch (Exception $e) {
            return ApiResponse::error('Error inesperado: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Cerrar sesión de usuario.
     *
     * @authenticated
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cerrarSesion(Request $request)
    {
        try {
            if (Auth::check()) {
                $request->user()->currentAccessToken()->delete();
                return ApiResponse::success('Cierre de sesión exitoso', 200);
            }

            return ApiResponse::error('El usuario no está logueado', 400);
        } catch (Exception $e) {
            return ApiResponse::error('Error inesperado: ' . $e->getMessage(), 400);
        }
    }
}

