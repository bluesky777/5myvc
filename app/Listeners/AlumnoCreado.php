<?php

namespace App\Listeners;

use App\Events\MatriculasEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlumnoCreado implements ShouldQueue
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
     * @param  MatriculasEvent  $event
     * @return void
     */
    public function handle(MatriculasEvent $event)
    {
        \Log::info('Un alumno fue creado');
    }
}
