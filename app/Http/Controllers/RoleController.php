<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\error;

/**
 * @group Roles
 *
 * Controlador para administrar roles en la aplicación.
 */
class RoleController extends Controller
{
    /**
     * Obtener todos los roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $roles = Role::all();
            return ApiResponse::success('Roles obtenidos correctamente', 200, $roles);
        } catch (\Exception $e) {
            return ApiResponse::error('Error inesperado: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Crear un nuevo rol.
     *
     * @bodyParam name string required Nombre único del rol.
     * @bodyParam guard_name string Guard name del rol (opcional).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                "name" => 'required|string|unique:roles,name',
                "guard_name" => "nullable",
            ]);

            $role = Role::create($request->all());
            return ApiResponse::success('Rol guardado correctamente', 200, $role);
        } catch (ValidationException $e) {
            return ApiResponse::error('Error de validación: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Obtener un rol por su ID.
     *
     * @urlParam id string required ID del rol.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $role = Role::findOrFail($id);
            return ApiResponse::success('Rol obtenido correctamente', 200, $role);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Rol no encontrado', 400);
        }
    }

    /**
     * Actualizar un rol existente.
     *
     * @urlParam id string required ID del rol a actualizar.
     * @bodyParam name string required Nuevo nombre del rol.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            $role = Role::findOrFail($id);

            $request->validate([
                "name" => "required|string",
            ]);

            $role->update($request->all());
            return ApiResponse::success('Rol actualizado correctamente', 200, $role);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('El rol no existe', 400);
        } catch (ValidationException $e) {
            return ApiResponse::error('Error de validación: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Eliminar un rol existente.
     *
     * @urlParam id string required ID del rol a eliminar.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            // Antes de eliminar el rol, se desactiva temporalmente el despachador de eventos para el modelo Role.
            Role::unsetEventDispatcher();
            $role = Role::findOrFail($id);
            $role->delete();

            return ApiResponse::success('Rol eliminado correctamente', 200, $role);
        } catch (\Exception $e) {
            return ApiResponse::error('Rol no encontrado: ' . $e->getMessage(), 400);
        }
    }
}
