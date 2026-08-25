<?php

namespace App\Providers;

use App\Events\UserPaid;
use App\Listeners\HandleUserPaid;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserPaid::class => [
            HandleUserPaid::class,
        ],
    ];

    public function boot(): void
    {
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
