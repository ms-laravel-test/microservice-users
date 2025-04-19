<?php

namespace App\Http\Controllers;

use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    //Todo: add listener for create,delete,update post and update redis
    /**
     * @throws \Exception
     */
    public function show($userId): JsonResponse
    {
        return resolve(PostService::class)->show($userId);
    }
}
