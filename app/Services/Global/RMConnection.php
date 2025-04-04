<?php

namespace App\Services\Global;

use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RMConnection
{
    protected AMQPStreamConnection $connection;
    protected AbstractChannel|AMQPChannel $channel;

    /**
     * RMConnection constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->initializeConnection();
    }

    /**
     * Initialize RabbitMQ connection and channel.
     * @throws Exception
     */
    protected function initializeConnection(): void
    {
        try {
            $this->connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST', 'rabbitmq'),
                env('RABBITMQ_PORT', 5672),
                env('RABBITMQ_USER', 'guest'),
                env('RABBITMQ_PASSWORD', 'guest')
            );
            $this->channel = $this->connection->channel();
        } catch (Exception $e) {
            Log::error('RabbitMQ Connection Failed: ' . $e->getMessage());
            throw new Exception('Could not connect to RabbitMQ');
        }
    }

    /**
     * Send a request to RabbitMQ.
     * @throws Exception
     */
    public function sendRequest(string $queue, array $data): array
    {

        list($callbackQueue, ,) = $this->channel->queue_declare("", false, false, true, false);

        $correlationId = uniqid();
        $msg = new AMQPMessage(
            json_encode($data),
            ['correlation_id' => $correlationId, 'reply_to' => $callbackQueue]
        );

        $this->channel->basic_publish($msg, '', $queue);

        return $this->waitForResponse($callbackQueue, $correlationId);
    }

    /**
     * @throws Exception
     */
    protected function waitForResponse(string $callbackQueue, string $correlationId): array
    {

        $response = null;

        $callback = function (AMQPMessage $msg) use (&$response, $correlationId) {
            if ($msg->get('correlation_id') === $correlationId) {
                $response = json_decode($msg->body, true);
            }
        };

        $this->channel->basic_consume($callbackQueue, '', false, true, false, false, $callback);

        $timeout = 60;
        $startTime = time();

        try {
            while (!$response) {
                $this->channel->wait();
                if (time() - $startTime >= $timeout) {
                    Log::error('RabbitMQ Response Timeout');
                    throw new Exception('Timeout: No response received from RabbitMQ');
                }
            }
        } finally {
            $this->closeConnection();
        }

        return $response;
    }

    /**
     * @throws Exception
     */
    public function closeConnection(): void
    {
        $this->channel?->close();
        $this->connection?->close();
    }

}
