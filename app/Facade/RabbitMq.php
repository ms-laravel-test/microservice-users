<?php

namespace App\Facade;

use Illuminate\Support\Facades\Facade;

class RabbitMq extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rabbitmq';
    }
}
