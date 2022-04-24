<?php

namespace Pterodactyl\Providers;

use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use Pterodactyl\Events\Server\Installed as ServerInstalledEvent;
use Pterodactyl\Notifications\ServerInstalled as ServerInstalledNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ServerInstalledEvent::class => [
            ServerInstalledNotification::class,
        ],
        SocialiteWasCalled::class => [
            DiscordExtendSocialite::class.'@handle',
        ],
    ];
}
