<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PostController extends Controller
{
    /**
     * @throws \Exception
     */
    public function show($userId): JsonResponse
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        list($callbackQueue, ,) = $channel->queue_declare("", false, false, true, false);

        $data = json_encode(['user_id' => $userId]);
        $correlationId = uniqid();

        $msg = new AMQPMessage(
            $data,
            [
                'correlation_id' => $correlationId,
                'reply_to' => $callbackQueue
            ]
        );

        $channel->basic_publish($msg, '', 'get_user_posts');

        $response = null;

        $callback = function ($msg) use (&$response, $correlationId) {
            if ($msg->get('correlation_id') == $correlationId) {
                $response = json_decode($msg->body, true);
            }
        };

        $channel->basic_consume($callbackQueue, '', false, true, false, false, $callback);

        while (!$response) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        return response()->json($response);
    }
}
