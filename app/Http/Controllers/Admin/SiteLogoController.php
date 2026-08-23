<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
    ];

    public function update(Request $request, string $logo): RedirectResponse
    {
        $config = $this->logoConfig($logo);

        $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        $oldPaths = $this->storedPaths($config);
        $path = MediaStorage::store($request->file('logo'), 'logos');

        AppSetting::setValue($config['key'], $path);
        $this->forgetLegacySetting($config);
        $this->deleteStoredPaths($oldPaths, $path);

        return back()->with('success', $config['label'].' zostało zaktualizowane.');
    }

    public function destroy(string $logo): RedirectResponse
    {
        $config = $this->logoConfig($logo);
        $oldPaths = $this->storedPaths($config);

        AppSetting::query()->where('key', $config['key'])->delete();
        $this->forgetLegacySetting($config);
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

    private function deleteStoredPaths(array $paths, ?string $except = null): void
    {
        foreach ($paths as $path) {
            if ($path !== $except) {
                MediaStorage::delete($path);
            }
        }
    }
}
