<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\ShippingProviderInterface;
use App\Models\AppSetting;
use App\Models\SponsorCategory;
use App\Models\User;
use App\Rules\NotCommonPassword;
use App\Support\BrowserPageTitle;
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
            static $logoData = null;

            if ($logoData === null) {
                $legacySiteLogoPath = AppSetting::getValue('site_logo');
                $clubLogoPath = AppSetting::getValue('club_logo') ?? $legacySiteLogoPath;
                $titleSponsorLogoPath = AppSetting::getValue('title_sponsor_logo');
                $titleSponsorUrl = AppSetting::getValue('title_sponsor_url');
                $academyLogoPath = AppSetting::getValue('academy_logo');
                $shopLogoPath = AppSetting::getValue('shop_logo');
                $ticketsLogoPath = AppSetting::getValue('tickets_logo');
                $adminLogoPath = AppSetting::getValue('admin_logo');
                $authLogoPath = AppSetting::getValue('auth_logo');
                $browserLogoPath = AppSetting::getValue('browser_logo');
                $browserIconPath = $browserLogoPath ?? $clubLogoPath;

                $logoData = [
                    'clubLogoPath' => $clubLogoPath,
                    'clubLogoUrl' => MediaStorage::url($clubLogoPath),
                    'titleSponsorLogoPath' => $titleSponsorLogoPath,
                    'titleSponsorLogoUrl' => MediaStorage::url($titleSponsorLogoPath),
                    'titleSponsorUrl' => $titleSponsorUrl,
                    'academyLogoPath' => $academyLogoPath,
                    'academyLogoUrl' => MediaStorage::url($academyLogoPath),
                    'shopLogoPath' => $shopLogoPath,
                    'shopLogoUrl' => MediaStorage::url($shopLogoPath),
                    'ticketsLogoPath' => $ticketsLogoPath,
                    'ticketsLogoUrl' => MediaStorage::url($ticketsLogoPath),
                    'adminLogoPath' => $adminLogoPath,
                    'adminLogoUrl' => MediaStorage::url($adminLogoPath),
                    'authLogoPath' => $authLogoPath,
                    'authLogoUrl' => MediaStorage::url($authLogoPath),
                    'browserLogoPath' => $browserLogoPath,
                    'browserLogoUrl' => MediaStorage::url($browserLogoPath),
                    'browserIconPath' => $browserIconPath,
                    'browserIconUrl' => MediaStorage::url($browserIconPath),
                    'siteLogoPath' => $clubLogoPath,
                    'siteLogoUrl' => MediaStorage::url($clubLogoPath),
                ];
            }

            foreach ($logoData as $key => $value) {
                $view->with($key, $value);
            }

            $view->with('browserBrandName', 'ETB Łódź');
            $view->with('browserPageTitle', BrowserPageTitle::fromRequest(request()));
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
