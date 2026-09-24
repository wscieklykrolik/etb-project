<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyGroup;
use App\Models\AcademyMessage;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcademyMessageController extends Controller
{
    public function __construct(private readonly AdminNotificationService $notificationService) {}

    public function store(Request $request, AcademyGroup $group): RedirectResponse
    {
        $message = $group->messages()->create($this->validated($request));
        $this->notificationService->record($request->user(), 'created', $message, "Komunikat akademii: {$message->title}");

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Komunikat akademii został zapisany.');
    }

    public function update(Request $request, AcademyMessage $message): RedirectResponse
    {
        $message->update($this->validated($request));
        $this->notificationService->record($request->user(), 'updated', $message, "Komunikat akademii: {$message->title}");

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Komunikat akademii został zaktualizowany.');
    }

    public function destroy(AcademyMessage $message): RedirectResponse
    {
        abort_unless(request()->user()?->role === User::ROLE_ADMIN, 403);

        $label = "Komunikat akademii: {$message->title}";
        $id = $message->id;
        $message->delete();
        $this->notificationService->recordDeleted(request()->user(), AcademyMessage::class, $id, $label);

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Komunikat akademii został usunięty.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['published_at'] ?? now();

        return $data;
    }
}
