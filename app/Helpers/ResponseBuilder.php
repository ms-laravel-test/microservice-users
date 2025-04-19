<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseBuilder
{
    public static function success($data = null, $message = 'Success', $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function error($message = 'Error', $status = 500, $data = null): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }
}
