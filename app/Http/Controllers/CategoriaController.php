<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Categoria;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use function Laravel\Prompts\error;

/**
 * @group Categorias
 *
 * Controlador para administrar categorías en la aplicación.
 */
class CategoriaController extends Controller
{
    /**
     * Obtener la lista de todas las categorías.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $categorias = Categoria::all();
            return ApiResponse::success('Lista de Categorias', 200, $categorias);
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener la lista de categorías: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear una nueva categoría.
     *
     * @bodyParam nombre string required Nombre único de la categoría.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:categorias',
            ]);

            $categoria = Categoria::create($request->all());
            return ApiResponse::success('Registro creado correctamente', 201, $categoria);
        } catch (ValidationException $e) {
            return ApiResponse::error('Error de validación: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Obtener detalles de una categoría por su ID.
     *
     * @urlParam id string required ID de la categoría.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            return ApiResponse::success('Categoría obtenida correctamente', 200, $categoria);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 400);
        }
    }

    /**
     * Actualizar información de una categoría existente.
     *
     * @urlParam id string required ID de la categoría a actualizar.
     * @bodyParam nombre string required Nuevo nombre de la categoría.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $request->validate([
                'nombre' => ['required', Rule::unique('categorias')->ignore($categoria)],
            ]);
            $categoria->update($request->all());

            return ApiResponse::success('Categoría actualizada correctamente', 200, $categoria);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 400);
        } catch (Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Eliminar una categoría existente.
     *
     * @urlParam id string required ID de la categoría a eliminar.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();

            return ApiResponse::success('Categoría eliminada correctamente', 200, $categoria);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 400);
        } catch (Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Obtener todos los productos asociados a una categoría.
     *
     * @urlParam id string required ID de la categoría.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function productosPorCategoria($id)
    {
        try {
            $categoria = Categoria::with('productos')->findOrFail($id);
            return ApiResponse::success('Categoría y lista de productos', 200, $categoria);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 400);
        }
    }
}

