<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchResultReminderDismissal;
use App\Models\TeamMatch;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MatchResultReminderController extends Controller
{
    public function __construct(private readonly AdminNotificationService $notificationService) {}

    public function store(Request $request, TeamMatch $match): RedirectResponse
    {
        abort_unless($match->isFinished(), 422);

        $validated = $request->validate([
            'our_score' => ['required', 'integer', 'min:0', 'max:999'],
            'opponent_score' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $match->update([
            ...$validated,
            'status' => TeamMatch::STATUS_FINISHED,
        ]);

        MatchResultReminderDismissal::query()
            ->where('user_id', $request->user()->id)
            ->where('match_id', $match->id)
            ->delete();

        $this->notificationService->record(
            $request->user(),
            'updated',
            $match,
            "Wynik meczu: ETB - {$match->opponent_name}"
        );

        return redirect()
            ->route('profile.edit', ['section' => 'matches'])
            ->with('success', 'Wynik meczu został zapisany.');
    }

    public function remind(Request $request, TeamMatch $match): RedirectResponse
    {
        abort_unless($this->needsResult($match), 422);

        $request->session()->flash('match_result_reminder_snoozed', [$match->id]);

        return redirect()
            ->route('profile.edit')
            ->with('info', 'Przypomnienie pojawi się ponownie przy następnym wejściu do panelu administratora.');
    }

    public function dismiss(Request $request, TeamMatch $match): RedirectResponse
    {
        abort_unless($this->needsResult($match), 422);

        MatchResultReminderDismissal::query()->updateOrCreate([
            'user_id' => $request->user()->id,
            'match_id' => $match->id,
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('info', 'Przypomnienie o wyniku tego meczu zostało odrzucone na zawsze.');
    }

    private function needsResult(TeamMatch $match): bool
    {
        return $match->has_time
            && $match->match_date?->lte(now()->subHours(2)) === true
            && ! $match->hasResult();
    }
}
