<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Marca;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @group Marcas
 *
 * Controlador para administrar marcas en la aplicación.
 */
class MarcaController extends Controller
{
    /**
     * Obtener la lista de todas las marcas.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $marcas = Marca::all();
            return ApiResponse::success('Lista de Marcas', 200, $marcas);
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener la lista de marcas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear una nueva marca.
     *
     * @bodyParam nombre string required Nombre único de la marca.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:marcas',
            ]);

            $marca = Marca::create($request->all());
            return ApiResponse::success('Marca creada correctamente', 201, $marca);
        } catch (ValidationException $e) {
            return ApiResponse::error('Error de validación: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Obtener detalles de una marca por su ID.
     *
     * @urlParam id string required ID de la marca.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $marca = Marca::findOrFail($id);
            return ApiResponse::success('Marca obtenida correctamente', 200, $marca);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 400);
        }
    }

    /**
     * Actualizar una marca existente.
     *
     * @urlParam id string required ID de la marca a actualizar.
     * @bodyParam nombre string required Nuevo nombre de la marca (debe ser único).
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            $marca = Marca::findOrFail($id);

            $request->validate([
                'nombre' => ['required', Rule::unique('marcas')->ignore($id)],
            ]);

            $marca->update($request->all());

            return ApiResponse::success('Marca actualizada correctamente', 200, $marca);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 400);
        } catch (ValidationException $e) {
            return ApiResponse::error('Error de validación: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Eliminar una marca existente.
     *
     * @urlParam id string required ID de la marca a eliminar.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $marca->delete();

            return ApiResponse::success('Marca eliminada correctamente', 200, $marca);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 400);
        } catch (Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Obtener los productos asociados a una marca.
     *
     * @urlParam id string required ID de la marca.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function productosPorMarca($id)
    {
        try {
            $marca = Marca::with('productos')->findOrFail($id);
            return ApiResponse::success('Marca y productos obtenidos correctamente', 200, $marca);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 400);
        }
    }
}
