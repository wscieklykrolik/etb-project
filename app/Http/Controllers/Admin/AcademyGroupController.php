<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyGroup;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademyGroupController extends Controller
{
    public function __construct(private readonly AdminNotificationService $notificationService) {}

    public function store(Request $request): RedirectResponse
    {
        $group = AcademyGroup::query()->create($this->validated($request));
        $this->notificationService->record($request->user(), 'created', $group, "Akademia: {$group->code}");

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Grupa akademii została zapisana.');
    }

    public function update(Request $request, AcademyGroup $group): RedirectResponse
    {
        $group->update($this->validated($request, $group));
        $this->notificationService->record($request->user(), 'updated', $group, "Akademia: {$group->code}");

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Grupa akademii została zaktualizowana.');
    }

    public function destroy(AcademyGroup $group): RedirectResponse
    {
        abort_unless(request()->user()?->role === User::ROLE_ADMIN, 403);

        $label = "Akademia: {$group->code}";
        $id = $group->id;
        $group->delete();
        $this->notificationService->recordDeleted(request()->user(), AcademyGroup::class, $id, $label);

        return redirect()->route('profile.edit', ['section' => 'academy'])->with('success', 'Grupa akademii została usunięta.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?AcademyGroup $group = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40', Rule::unique('academy_groups', 'code')->ignore($group)],
            'color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (in_array(mb_strtolower((string) $value), ['#000000', '#ffffff'], true)) {
                        $fail('Czarny i biały kolor są zarezerwowane dla interfejsu strony.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = mb_strtoupper($data['code']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
