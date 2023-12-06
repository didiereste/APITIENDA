<?php

namespace App\Http\Responses;

/**
 * Class ApiResponse
 *
 * Clase para manejar las respuestas JSON de la API.
 */
class ApiResponse
{
    /**
     * Genera una respuesta exitosa en formato JSON.
     *
     * @param string $message Mensaje descriptivo de la respuesta.
     * @param int $statusCode Código de estado HTTP de la respuesta.
     * @param array $data Datos adicionales asociados con la respuesta.
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON.
     */
    public static function success($message = 'Success', $statusCode = 200, $data = [])
    {
        return response()->json([
            'message' => $message,
            'statusCode' => $statusCode,
            'error' => false,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Genera una respuesta de error en formato JSON.
     *
     * @param string $message Mensaje descriptivo del error.
     * @param int $statusCode Código de estado HTTP de la respuesta.
     * @param array $data Datos adicionales asociados con el error.
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON.
     */
    public static function error($message = 'Error', $statusCode, $data = [])
    {
        return response()->json([
            'message' => $message,
            'statusCode' => $statusCode,
            'error' => true,
            'data' => $data
        ], $statusCode);
    }
}
