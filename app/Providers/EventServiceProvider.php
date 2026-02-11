<?php

namespace App\Providers;

use App\Listeners\CreateDefaultCategories;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventService extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            CreateDefaultCategories::class
        ]
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
