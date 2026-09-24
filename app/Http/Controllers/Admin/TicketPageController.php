<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketPageController extends Controller
{
    private const IMAGE_KEY = 'tickets_page_image';
    private const BODY_KEY = 'tickets_page_body';
    private const BUTTON_URL_KEY = 'tickets_page_button_url';
    private const BUTTON_LABEL_KEY = 'tickets_page_button_label';

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:100000'],
            'button_url' => ['nullable', 'url', 'max:2048'],
            'button_label' => ['nullable', 'string', 'max:80'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
        ], [], [
            'body' => 'treść',
            'button_url' => 'link przycisku',
            'button_label' => 'podpis przycisku',
            'image' => 'grafika',
            'remove_image' => 'usunięcie grafiki',
        ]);

        $oldPath = AppSetting::getValue(self::IMAGE_KEY);
        $newPath = $request->hasFile('image')
            ? MediaStorage::store($request->file('image'), 'tickets')
            : null;

        try {
            $this->setOrDelete(self::BODY_KEY, $data['body'] ?? null);
            $this->setOrDelete(self::BUTTON_URL_KEY, $data['button_url'] ?? null);
            $this->setOrDelete(self::BUTTON_LABEL_KEY, $data['button_label'] ?? null);

            if ($newPath !== null) {
                AppSetting::setValue(self::IMAGE_KEY, $newPath);
            } elseif ($request->boolean('remove_image')) {
                AppSetting::query()->where('key', self::IMAGE_KEY)->delete();
            }
        } catch (\Throwable $exception) {
            MediaStorage::delete($newPath);
            throw $exception;
        }

        $currentPath = AppSetting::getValue(self::IMAGE_KEY);

        if ($oldPath !== $currentPath) {
            MediaStorage::delete($oldPath);
        }

        return redirect()
            ->route('profile.edit', ['section' => 'tickets'])
            ->with('success', 'Strona biletów została zaktualizowana.');
    }

    private function setOrDelete(string $key, ?string $value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            AppSetting::query()->where('key', $key)->delete();

            return;
        }

        AppSetting::setValue($key, $value);
    }
}
