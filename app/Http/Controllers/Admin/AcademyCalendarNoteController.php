<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyCalendarNote;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcademyCalendarNoteController extends Controller
{
    public function __construct(private readonly AdminNotificationService $notificationService) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:3000'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);

        $note = AcademyCalendarNote::query()->create($data);
        $this->notificationService->record($request->user(), 'created', $note, "Wpis w kalendarzu akademii: {$note->title}");

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Wpis w kalendarzu akademii został zapisany.');
    }

    public function destroy(AcademyCalendarNote $note): RedirectResponse
    {
        abort_unless(request()->user()?->role === User::ROLE_ADMIN, 403);

        $label = "Wpis w kalendarzu akademii: {$note->title}";
        $id = $note->id;
        $note->delete();
        $this->notificationService->recordDeleted(request()->user(), AcademyCalendarNote::class, $id, $label);

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Wpis w kalendarzu akademii został usunięty.');
    }
}
