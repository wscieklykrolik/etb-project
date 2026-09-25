<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\ShippingProviderInterface;
use App\Models\AppSetting;
use App\Models\SponsorCategory;
use App\Models\User;
use App\Rules\NotCommonPassword;
use App\Services\DpdShippingProvider;
use App\Services\InPostShippingProvider;
use App\Services\OrderNotificationService;
use App\Services\Przelewy24Gateway;
use App\Support\BrowserPageTitle;
use App\Support\MediaStorage;
use Illuminate\Http\Middleware\TrustProxies;
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
        TrustProxies::at(config('security.trusted_proxies') ?: []);

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
            if (str_starts_with($view->name(), 'errors')) {
                return;
            }

            $logoData = request()->attributes->get('shared_logo_data');

            if ($logoData === null) {
                $legacySiteLogoPath = AppSetting::getValue('site_logo');
                $clubLogoPath = AppSetting::getValue('club_logo') ?? $legacySiteLogoPath;
                $titleSponsorLogoPath = AppSetting::getValue('title_sponsor_logo');
                $titleSponsorUrl = AppSetting::getValue('title_sponsor_url');
                $publicTeamName = AppSetting::getValue('public_team_name') ?: 'ETB';
                $academyLogoPath = AppSetting::getValue('academy_logo');
                $shopLogoPath = AppSetting::getValue('shop_logo');
                $ticketsLogoPath = AppSetting::getValue('tickets_logo');
                $ticketsPageImagePath = AppSetting::getValue('tickets_page_image');
                $adminLogoPath = AppSetting::getValue('admin_logo');
                $authLogoPath = AppSetting::getValue('auth_logo');
                $browserLogoPath = AppSetting::getValue('browser_logo');
                $browserLightLogoPath = AppSetting::getValue('browser_light_logo');
                $browserDarkLogoPath = AppSetting::getValue('browser_dark_logo');
                $browserIconPath = $browserLogoPath ?? $browserLightLogoPath ?? $browserDarkLogoPath ?? $clubLogoPath;

                $logoData = [
                    'clubLogoPath' => $clubLogoPath,
                    'clubLogoUrl' => MediaStorage::url($clubLogoPath),
                    'titleSponsorLogoPath' => $titleSponsorLogoPath,
                    'titleSponsorLogoUrl' => MediaStorage::url($titleSponsorLogoPath),
                    'titleSponsorUrl' => $titleSponsorUrl,
                    'publicTeamName' => $publicTeamName,
                    'academyLogoPath' => $academyLogoPath,
                    'academyLogoUrl' => MediaStorage::url($academyLogoPath),
                    'shopLogoPath' => $shopLogoPath,
                    'shopLogoUrl' => MediaStorage::url($shopLogoPath),
                    'ticketsLogoPath' => $ticketsLogoPath,
                    'ticketsLogoUrl' => MediaStorage::url($ticketsLogoPath),
                    'ticketsPageImagePath' => $ticketsPageImagePath,
                    'ticketsPageImageUrl' => MediaStorage::url($ticketsPageImagePath),
                    'ticketsPageBody' => AppSetting::getValue('tickets_page_body'),
                    'ticketsPageButtonUrl' => AppSetting::getValue('tickets_page_button_url'),
                    'ticketsPageButtonLabel' => AppSetting::getValue('tickets_page_button_label'),
                    'adminLogoPath' => $adminLogoPath,
                    'adminLogoUrl' => MediaStorage::url($adminLogoPath),
                    'authLogoPath' => $authLogoPath,
                    'authLogoUrl' => MediaStorage::url($authLogoPath),
                    'browserLogoPath' => $browserLogoPath,
                    'browserLogoUrl' => MediaStorage::url($browserLogoPath),
                    'browserLightLogoPath' => $browserLightLogoPath,
                    'browserLightLogoUrl' => MediaStorage::url($browserLightLogoPath),
                    'browserDarkLogoPath' => $browserDarkLogoPath,
                    'browserDarkLogoUrl' => MediaStorage::url($browserDarkLogoPath),
                    'browserIconPath' => $browserIconPath,
                    'browserIconUrl' => MediaStorage::url($browserIconPath),
                    'siteLogoPath' => $clubLogoPath,
                    'siteLogoUrl' => MediaStorage::url($clubLogoPath),
                ];

                request()->attributes->set('shared_logo_data', $logoData);
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
