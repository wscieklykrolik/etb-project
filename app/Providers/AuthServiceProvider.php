<?php

namespace App\Providers;

use App\Models\News;
use App\Models\Player;
use App\Policies\NewsPolicy;
use App\Policies\PlayerPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Player::class => PlayerPolicy::class,
        News::class => NewsPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
