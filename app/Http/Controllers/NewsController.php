<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class NewsController extends Controller
{
    public function index(): View { $this->authorize('viewAny', News::class); $newsItems = News::where(function ($q) { $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()); })->with('author')->latest()->get(); return view('news.index', compact('newsItems')); }
    public function show(News $news): View { $this->authorize('view', $news); $news->load('author'); return view('news.show', compact('news')); }
    public function create(): View { $this->authorize('create', News::class); return view('news.create'); }
    public function store(StoreNewsRequest $request): RedirectResponse { News::query()->create([...$request->validated(),'author_id' => $request->user()->id]); return back()->with('success', 'Changes saved successfully'); }
    public function edit(News $news): View { $this->authorize('update', $news); return view('news.edit', compact('news')); }
    public function update(UpdateNewsRequest $request, News $news): RedirectResponse { $news->update($request->validated()); return back()->with('success', 'Changes saved successfully'); }
    public function destroy(News $news): RedirectResponse { $this->authorize('delete', $news); $news->delete(); return back()->with('success', 'Changes saved successfully'); }
}
