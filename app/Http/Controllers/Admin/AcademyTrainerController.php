<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyGroup;
use App\Models\AcademyTrainer;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcademyTrainerController extends Controller
{
    public function __construct(private readonly AdminNotificationService $notificationService) {}

    public function store(Request $request, AcademyGroup $group): RedirectResponse
    {
        $trainer = $group->trainers()->create($this->validated($request));
        $this->notificationService->record($request->user(), 'created', $trainer, "Trener akademii: {$trainer->name}");

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Trener akademii został zapisany.');
    }

    public function suggestions(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $normalizedQuery = mb_strtolower($query);

        $trainers = AcademyTrainer::query()
            ->orderBy('name')
            ->get(['name', 'role', 'email', 'phone'])
            ->filter(fn (AcademyTrainer $trainer): bool => str_contains(mb_strtolower($trainer->name), $normalizedQuery))
            ->unique(fn (AcademyTrainer $trainer): string => mb_strtolower($trainer->name).'|'.($trainer->phone ?? ''))
            ->values()
            ->take(8)
            ->map(fn (AcademyTrainer $trainer): array => [
                'name' => $trainer->name,
                'role' => $trainer->role,
                'email' => $trainer->email,
                'phone' => $trainer->phone,
            ]);

        return response()->json($trainers);
    }

    public function update(Request $request, AcademyTrainer $trainer): RedirectResponse
    {
        $trainer->update($this->validated($request));
        $this->notificationService->record($request->user(), 'updated', $trainer, "Trener akademii: {$trainer->name}");

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Trener akademii został zaktualizowany.');
    }

    public function destroy(AcademyTrainer $trainer): RedirectResponse
    {
        abort_unless(request()->user()?->role === User::ROLE_ADMIN, 403);

        $label = "Trener akademii: {$trainer->name}";
        $id = $trainer->id;
        $trainer->delete();
        $this->notificationService->recordDeleted(request()->user(), AcademyTrainer::class, $id, $label);

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Trener akademii został usunięty.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
