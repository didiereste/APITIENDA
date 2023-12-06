<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Compra;
use App\Models\Producto;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @group Compras
 *
 * Controlador para administrar compras en la aplicación.
 */
class CompraController extends Controller
{
    /**
     * Obtener la lista de todas las compras.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $compras = Compra::all();
            return ApiResponse::success('Lista de compras cargadas correctamente', 200, $compras);
        } catch (Exception $e) {
            return ApiResponse::error('Error al cargar las compras', 400);
        }
    }

    /**
     * Realizar una nueva compra.
     *
     * @bodyParam productos array required Lista de productos a comprar.
     * @bodyParam productos.*.producto_id integer required ID del producto.
     * @bodyParam productos.*.cantidad integer required Cantidad del producto a comprar (debe ser mayor que 0).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $productos = $request->input('productos');

            // Validar los productos 
            if (empty($productos)) {
                return ApiResponse::error('No se proporcionaron productos', 400);
            }

            // Validar la lista de productos
            $validator = Validator::make($request->all(), [
                'productos' => 'required|array',
                'productos.*.producto_id' => 'required|integer|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1',
            ]);

            // Validar que la validación de arriba no tenga errores
            if ($validator->fails()) {
                return ApiResponse::error('Datos inválidos en la lista de productos', 400, $validator->errors());
            }

            // Validar que no existan productos duplicados
            $productoIds = array_column($productos, 'producto_id');
            if (count($productoIds) !== count(array_unique($productoIds))) {
                return ApiResponse::error('No se permiten productos duplicados para la compra', 400);
            }

            $totalPagar = 0;
            $subtotal = 0;
            $compraItems = [];

            // Iteración de los productos para calcular el total a pagar de la compra
            foreach ($productos as $producto) {
                $productoB = Producto::find($producto['producto_id']);
                if (!$productoB) {
                    return ApiResponse::error('Producto no encontrado', 404);
                }

                // Validar la cantidad de productos solicitados si la hay en la base de datos
                if ($productoB->cantidad_disponible < $producto['cantidad']) {
                    return ApiResponse::error('No hay suficiente cantidad', 404);
                }

                // En caso de que sí la haya, actualizar el stock descontando la cantidad que compraron
                $productoB->cantidad_disponible = $productoB->cantidad_disponible - $producto['cantidad'];
                $productoB->save();

                // Cálculo de los importes
                $subtotal = $productoB->precio * $producto['cantidad'];
                $totalPagar = $totalPagar + $subtotal;

                // Guardar los items de la compra
                $compraItems[] = [
                    'producto_id' => $productoB->id,
                    'precio' => $productoB->precio,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $subtotal,
                ];
            }

            // Registrar la compra 
            $compra = Compra::create([
                'subtotal' => $totalPagar,
                'total' => $totalPagar,
            ]);

            // Asociar los productos a la compra con sus cantidades y subtotales a la tabla intermedia
            $compra->productos()->attach($compraItems);

            return ApiResponse::success('Compra realizada correctamente', 201, $compra);
        } catch (QueryException $e) {
            return ApiResponse::error('Error en la consulta de la base de datos: ' . $e->getMessage(), 500);
        } catch (Exception $e) {
            return ApiResponse::error('Error inesperado: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener detalles de una compra por su ID.
     *
     * @urlParam id string required ID de la compra.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $compra = Compra::findOrFail($id);
            return ApiResponse::success('Compra cargada correctamente', 200, $compra);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Compra no encontrada', 400);
        }
    }

    /**
     * Actualizar información de una compra existente.
     *
     * @urlParam id string required ID de la compra a actualizar.
     * @bodyParam subtotal integer required Nuevo subtotal de la compra.
     * @bodyParam total integer required Nuevo total de la compra.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            $compra = Compra::findOrFail($id);

            $request->validate([
                "subtotal" => "required|integer",
                "total" => "required|integer",
            ]);

            $compra->update($request->all());
            return ApiResponse::success('Compra actualizada correctamente', 200, $compra);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Compra no encontrada: ' . $e->getMessage(), 400);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validación incorrecta: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Eliminar una compra existente.
     *
     * @urlParam id string required ID de la compra a eliminar.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $compra = Compra::findOrFail($id);
            $compra->delete();
            return ApiResponse::success('Compra eliminada correctamente', 200, $compra);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Compra no encontrada', 400);
        }
    }
}
