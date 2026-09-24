<?php

namespace App\Http\Controllers;

use App\Models\ImportantPage;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportantPageController extends Controller
{
    public function show(string $slug): View
    {
        abort_unless(array_key_exists($slug, ImportantPage::PAGES), 404);

        return view('pages.important-page', [
            'title' => ImportantPage::PAGES[$slug],
            'page' => ImportantPage::firstOrNew(['slug' => $slug]),
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        abort_unless(array_key_exists($slug, ImportantPage::PAGES), 404);

        $data = $request->validateWithBag($slug, [
            'body' => ['nullable', 'string', 'max:100000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
        ], [], [
            'body' => 'treść',
            'image' => 'zdjęcie',
            'remove_image' => 'usunięcie zdjęcia',
        ]);

        $page = ImportantPage::firstOrNew(['slug' => $slug]);
        $oldPath = $page->image_path;
        $newPath = $request->hasFile('image')
            ? MediaStorage::store($request->file('image'), 'important-pages')
            : null;
        $page->body = $data['body'] ?? null;
        $page->image_path = $newPath ?? ($request->boolean('remove_image') ? null : $oldPath);

        try {
            $page->save();
        } catch (\Throwable $exception) {
            MediaStorage::delete($newPath);
            throw $exception;
        }

        if ($oldPath !== $page->image_path) {
            MediaStorage::delete($oldPath);
        }

        return redirect()->route('profile.edit', ['section' => 'important-links'])
            ->with('success', 'Zapisano stronę: '.ImportantPage::PAGES[$slug].'.');
    }
}
