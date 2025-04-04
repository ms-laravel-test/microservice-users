<?php

namespace App\Http\Controllers;

use App\Facade\RabbitMq;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * @throws \Exception
     */
    public function show($userId): JsonResponse
    {
        try {
            $response = RabbitMq::sendRequest('get_user_posts', ['user_id' => $userId]);

            return response()->json($response);
        } catch (Exception $e) {
            Log::error('Error in RMConnection', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Could not get user posts'], 500);
        }
    }
}
