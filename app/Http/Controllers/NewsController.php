<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', News::class);

        $newsItems = News::query()->with('author')->latest()->get();

        return view('news.index', compact('newsItems'));
    }

    public function show(News $news): View
    {
        $this->authorize('view', $news);

        $news->load('author');

        return view('news.show', compact('news'));
    }

    public function create(): View
    {
        $this->authorize('create', News::class);

        return view('news.create');
    }

    public function store(StoreNewsRequest $request): RedirectResponse
    {
        $news = News::query()->create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
        ]);

        return redirect()->route('news.show', $news);
    }

    public function edit(News $news): View
    {
        $this->authorize('update', $news);

        return view('news.edit', compact('news'));
    }

    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $news->update($request->validated());

        return redirect()->route('news.show', $news);
    }

    public function destroy(News $news): RedirectResponse
    {
        $this->authorize('delete', $news);

        $news->delete();

        return redirect()->route('news.index');
    }
}
