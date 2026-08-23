<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteLogoController extends Controller
{
    private const LOGOS = [
        'club' => [
            'key' => 'club_logo',
            'label' => 'Logo ETB Łódź',
            'legacy_key' => 'site_logo',
        ],
        'title-sponsor' => [
            'key' => 'title_sponsor_logo',
            'label' => 'Logo sponsora tytularnego',
            'link_key' => 'title_sponsor_url',
        ],
        'academy' => [
            'key' => 'academy_logo',
            'label' => 'Logo akademii',
        ],
        'shop' => [
            'key' => 'shop_logo',
            'label' => 'Logo sklepu',
        ],
        'tickets' => [
            'key' => 'tickets_logo',
            'label' => 'Logo biletów',
        ],
        'admin' => [
            'key' => 'admin_logo',
            'label' => 'Logo panelu admina',
        ],
        'auth' => [
            'key' => 'auth_logo',
            'label' => 'Logo panelu logowania',
        ],
        'browser' => [
            'key' => 'browser_logo',
            'label' => 'Logo karty przeglądarki',
        ],
    ];

    public function update(Request $request, string $logo): RedirectResponse
    {
        $config = $this->logoConfig($logo);

        $request->validate([
            'logo' => [Rule::requiredIf(! AppSetting::getValue($config['key'])), 'nullable', 'image', 'max:2048'],
            'url' => ['nullable', 'url', 'max:255'],
        ]);

        if ($request->hasFile('logo')) {
            $oldPaths = $this->storedPaths($config);
            $path = MediaStorage::store($request->file('logo'), 'logos');

            AppSetting::setValue($config['key'], $path);
            $this->forgetLegacySetting($config);
            $this->deleteStoredPaths($oldPaths, $path);
        }

        if (isset($config['link_key'])) {
            if ($request->filled('url')) {
                AppSetting::setValue($config['link_key'], $request->string('url')->toString());
            } else {
                AppSetting::query()->where('key', $config['link_key'])->delete();
            }
        }

        return back()->with('success', $config['label'].' zostało zaktualizowane.');
    }

    public function destroy(string $logo): RedirectResponse
    {
        $config = $this->logoConfig($logo);
        $oldPaths = $this->storedPaths($config);

        AppSetting::query()->where('key', $config['key'])->delete();
        $this->forgetLegacySetting($config);
        $this->forgetLinkSetting($config);
        $this->deleteStoredPaths($oldPaths);

        return back()->with('success', $config['label'].' zostało usunięte.');
    }

    private function logoConfig(string $logo): array
    {
        abort_unless(array_key_exists($logo, self::LOGOS), 404);

        return self::LOGOS[$logo];
    }

    private function storedPaths(array $config): array
    {
        return collect([$config['key'], $config['legacy_key'] ?? null])
            ->filter()
            ->map(fn (string $key): ?string => AppSetting::getValue($key))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function forgetLegacySetting(array $config): void
    {
        if (isset($config['legacy_key'])) {
            AppSetting::query()->where('key', $config['legacy_key'])->delete();
        }
    }

    private function forgetLinkSetting(array $config): void
    {
        if (isset($config['link_key'])) {
            AppSetting::query()->where('key', $config['link_key'])->delete();
        }
    }

    private function deleteStoredPaths(array $paths, ?string $except = null): void
    {
        foreach ($paths as $path) {
            if ($path !== $except) {
                MediaStorage::delete($path);
            }
        }
    }
}
