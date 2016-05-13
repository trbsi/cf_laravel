<?php

namespace App\Listeners\Module;

use App\Events\Module\PostRegistrationEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostRegistrationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PostRegistrationEvent  $event
     * @return void
     */
    public function handle()
    {
        //
        file_put_contents("t.txt", "aaa");
    }
}
