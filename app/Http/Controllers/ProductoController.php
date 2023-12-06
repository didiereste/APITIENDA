<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Producto;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Events\Validated;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @group Productos
 *
 * Controlador para administrar productos en la aplicación.
 */
class ProductoController extends Controller
{
    /**
     * Obtener la lista de todos los productos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $productos = Producto::with('marca', 'categoria')->get();
            return ApiResponse::success('Lista de Productos', 200, $productos);
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener la lista de productos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear un nuevo producto.
     *
     * @bodyParam nombre string required Nombre único del producto.
     * @bodyParam precio numeric required Precio del producto (entre 0 y 999999.99).
     * @bodyParam cantidad_disponible integer required Cantidad disponible del producto.
     * @bodyParam categoria_id integer required ID de la categoría a la que pertenece el producto.
     * @bodyParam marca_id integer required ID de la marca del producto.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:productos',
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id',
            ]);

            $producto = Producto::create($request->all());

            return ApiResponse::success('Producto creado exitosamente', 201, $producto);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->toArray();

            if (isset($errors['categoria_id'])) {
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }

            if (isset($errors['marca_id'])) {
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }

            return ApiResponse::error('Error de validación: ', 422, $errors);
        }
    }

    /**
     * Obtener detalles de un producto por su ID.
     *
     * @urlParam id string required ID del producto.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $producto = Producto::with('marca', 'categoria')->findOrFail($id);
            return ApiResponse::success('Producto capturado correctamente', 200, $producto);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 400);
        }
    }

    /**
     * Actualizar un producto existente.
     *
     * @urlParam id string required ID del producto a actualizar.
     * @bodyParam nombre string required Nuevo nombre del producto (debe ser único).
     * @bodyParam precio numeric required Nuevo precio del producto (entre 0 y 999999.99).
     * @bodyParam cantidad_disponible integer required Nueva cantidad disponible del producto.
     * @bodyParam categoria_id integer required Nuevo ID de la categoría del producto.
     * @bodyParam marca_id integer required Nuevo ID de la marca del producto.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            $producto = Producto::findOrFail($id);

            $request->validate([
                'nombre' => ['required', Rule::unique('productos')->ignore($id)],
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id',
            ]);

            $producto->update($request->all());
            return ApiResponse::success('Producto actualizado correctamente', 200, $producto);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->toArray();
            return ApiResponse::error('Error de validación', 400, $errors);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 400);
        }
    }

    /**
     * Eliminar un producto existente.
     *
     * @urlParam id string required ID del producto a eliminar.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto->delete();

            return ApiResponse::success('Producto eliminado correctamente', 200, $producto);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 400);
        }
    }
}
