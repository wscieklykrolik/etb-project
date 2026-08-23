<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteLogoController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'site_logo' => ['required', 'image', 'max:2048'],
        ]);

        $oldPath = AppSetting::getValue('site_logo');
        $path = MediaStorage::store($request->file('site_logo'), 'logos');

        AppSetting::setValue('site_logo', $path);
        MediaStorage::delete($oldPath);

        return back()->with('success', 'Logo strony zostało zaktualizowane.');
    }

    public function destroy(): RedirectResponse
    {
        $oldPath = AppSetting::getValue('site_logo');

        AppSetting::query()->where('key', 'site_logo')->delete();
        MediaStorage::delete($oldPath);

        return back()->with('success', 'Logo strony zostało usunięte.');
    }
}
