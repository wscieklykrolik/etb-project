<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\ShippingProviderInterface;
use App\Models\AppSetting;
use App\Models\SponsorCategory;
use App\Models\User;
use App\Rules\NotCommonPassword;
use App\Support\MediaStorage;
use App\Services\DpdShippingProvider;
use App\Services\InPostShippingProvider;
use App\Services\OrderNotificationService;
use App\Services\Przelewy24Gateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayInterface::class, Przelewy24Gateway::class);
        $this->app->singleton(ShippingProviderInterface::class, function ($app) {
            return match (config('shipping.provider')) {
                'inpost' => new InPostShippingProvider,
                'dpd' => new DpdShippingProvider,
                default => new InPostShippingProvider,
            };
        });
        $this->app->singleton(OrderNotificationService::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn (): Password => Password::min((int) config('security.password.min_length', 15))
            ->max((int) config('security.password.max_length', 128))
            ->rules([new NotCommonPassword]));

        Gate::define('assign-roles', fn (User $user): bool => $user->isAdmin());

        Gate::define('access-admin-panel', fn (User $user): bool => $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE])
        );

        Gate::define('manage-players', fn (User $user): bool => $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE])
        );

        Gate::define('manage-news', fn (User $user): bool => $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE])
        );

        Gate::define('manage-matches', fn (User $user): bool => $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE])
        );

        View::composer('*', function ($view): void {
            static $siteLogoPath = null;
            static $siteLogoLoaded = false;

            if (! $siteLogoLoaded) {
                $siteLogoPath = AppSetting::getValue('site_logo');
                $siteLogoLoaded = true;
            }

            $view->with('siteLogoPath', $siteLogoPath);
            $view->with('siteLogoUrl', MediaStorage::url($siteLogoPath));
        });

        View::composer('partials.footer', function ($view): void {
            $view->with('footerSponsorCategories', SponsorCategory::query()
                ->active()
                ->whereHas('sponsors', fn ($query) => $query->active())
                ->with(['sponsors' => fn ($query) => $query
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get());
        });
    }
}
