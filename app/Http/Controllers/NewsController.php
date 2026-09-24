<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use App\Services\AdminNotificationService;
use App\Services\NewsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(
        private readonly NewsService $newsService,
        private readonly AdminNotificationService $notificationService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', News::class);

        $newsItems = News::query()
            ->with(['author', 'images'])
            ->active()
            ->published()
            ->latest('publish_at')
            ->latest()
            ->get();

        return view('news.index', compact('newsItems'));
    }

    public function show(News $news): View
    {
        $this->authorize('view', $news);

        $news->load(['author', 'images']);

        return view('news.show', compact('news'));
    }

    public function create(): View
    {
        $this->authorize('create', News::class);

        return view('news.create');
    }

    public function store(StoreNewsRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->safe()->except(['main_image', 'gallery']);
        $gallery = $request->file('gallery', []);

        $news = $this->newsService->create($data, $request->user()->id, $request->file('main_image'), $gallery);
        $this->notificationService->record($request->user(), 'created', $news, "Aktualność: {$news->title}");

        if ($request->expectsJson()) {
            $request->session()->flash('success', $news->is_draft ? 'Wersja robocza została zapisana.' : 'Aktualność została zapisana.');

            return response()->json(['redirect' => route('profile.edit', ['section' => 'news'])]);
        }

        return redirect()->route('profile.edit', ['section' => 'news'])->with('success', $news->is_draft ? 'Wersja robocza została zapisana.' : 'Aktualność została zapisana.');
    }

    public function edit(News $news): View
    {
        $this->authorize('update', $news);

        return view('news.edit', compact('news'));
    }

    public function update(UpdateNewsRequest $request, News $news): RedirectResponse|JsonResponse
    {
        $data = $request->safe()->except(['main_image', 'gallery']);
        $gallery = $request->file('gallery', []);

        $this->newsService->update($news, $data, $request->file('main_image'), $gallery);
        $this->notificationService->record($request->user(), 'updated', $news, "Aktualność: {$news->title}");

        if ($request->expectsJson()) {
            $request->session()->flash('success', $news->is_draft ? 'Wersja robocza została zapisana.' : 'Aktualność została zapisana.');

            return response()->json(['redirect' => route('profile.edit', ['section' => 'news'])]);
        }

        return redirect()->route('profile.edit', ['section' => 'news'])->with('success', $news->is_draft ? 'Wersja robocza została zapisana.' : 'Aktualność została zaktualizowana.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $this->authorize('delete', $news);

        $label = "Aktualność: {$news->title}";
        $id = $news->id;
        $this->newsService->delete($news);
        $this->notificationService->recordDeleted(request()->user(), News::class, $id, $label);

        return back()->with('success', 'Aktualność została usunięta.');
    }

    public function preview(News $news): View
    {
        $this->authorize('preview', $news);

        $news->load(['author', 'images']);

        return view('pages.news-show', [
            'news' => $news,
            'isPreview' => true,
        ]);
    }

    public function publish(News $news): RedirectResponse
    {
        $this->authorize('publish', $news);

        $this->newsService->publishNow($news);
        $this->notificationService->record(request()->user(), 'published', $news, "Aktualność: {$news->title}");

        return redirect()->route('profile.edit', ['section' => 'news'])->with('success', 'Aktualność została opublikowana.');
    }
}
