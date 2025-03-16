<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PostCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        \Log::info("New Post Received: ", (array) $event);
    }
}
