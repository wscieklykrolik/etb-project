<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqQuestion;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FaqQuestionController extends Controller
{
    public function __construct(private readonly AdminNotificationService $notificationService)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $question = FaqQuestion::query()->create($this->validated($request));
        $this->notificationService->record($request->user(), 'created', $question, "Pytanie: {$question->question}");

        return redirect()->route('profile.edit', ['section' => 'faq'])->with('success', 'Pytanie zostało zapisane.');
    }

    public function update(Request $request, FaqQuestion $question): RedirectResponse
    {
        $question->update($this->validated($request));
        $this->notificationService->record($request->user(), 'updated', $question, "Pytanie: {$question->question}");

        return redirect()->route('profile.edit', ['section' => 'faq'])->with('success', 'Pytanie zostało zaktualizowane.');
    }

    public function destroy(FaqQuestion $question): RedirectResponse
    {
        abort_unless(request()->user()?->role === User::ROLE_ADMIN, 403);

        $label = "Pytanie: {$question->question}";
        $id = $question->id;
        $question->delete();
        $this->notificationService->recordDeleted(request()->user(), FaqQuestion::class, $id, $label);

        return redirect()->route('profile.edit', ['section' => 'faq'])->with('success', 'Pytanie zostało usunięte.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:10000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['published_at'] ?? now();

        return $data;
    }
}
