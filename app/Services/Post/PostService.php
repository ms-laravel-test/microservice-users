<?php

namespace App\Services\Post;

use App\Facade\RabbitMq;
use App\Helpers\ResponseBuilder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PostService
{

    public function show($userId): JsonResponse
    {
        try {
            $cacheKey = "user_{$userId}_posts";

            if (Redis::exists($cacheKey)) {
                return $this->respondWithCache($cacheKey);
            }

            return $this->fetchAndCachePosts($userId, $cacheKey);

        } catch (Exception $e) {
            Log::error('Error in RMConnection', ['message' => $e->getMessage()]);
            return ResponseBuilder::error('Could not get user posts');
        }
    }

    protected function respondWithCache(string $key): JsonResponse
    {
        $cached = Redis::get($key);
        return ResponseBuilder::success(json_decode($cached));
    }

    protected function fetchAndCachePosts(int $userId, string $key): JsonResponse
    {
        $response = RabbitMq::sendRequest('get_user_posts', ['user_id' => $userId]);
        Redis::set($key, json_encode($response));
        return ResponseBuilder::success($response);
    }

}
