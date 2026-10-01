<?php

namespace App\Http\Requests;

use App\Models\TeamMatch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMatchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $matchDate = (string) $this->input('match_date', '');
        $matchTime = trim((string) $this->input('match_time', ''));
        $dateAlreadyContainsTime = preg_match('/[T ]\d{2}:\d{2}/', $matchDate) === 1;

        if ($matchTime !== '' && ! $dateAlreadyContainsTime) {
            $matchDate = substr($matchDate, 0, 10).' '.$matchTime;
        }

        $this->merge([
            'is_home' => $this->boolean('is_home'),
            'include_in_lzkosz' => $this->boolean('include_in_lzkosz'),
            'is_ticketed' => $this->boolean('is_ticketed'),
            'opponent_name' => $this->input('opponent_name', $this->input('opponent')),
            'match_date' => $matchDate,
            'has_time' => $matchTime !== '' || $dateAlreadyContainsTime,
        ]);
    }

    public function authorize(): bool
    {
        $match = $this->route('match');

        return $this->user()?->can('update', $match instanceof TeamMatch ? $match : TeamMatch::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([TeamMatch::STATUS_UPCOMING, TeamMatch::STATUS_FINISHED])],
            'season' => ['nullable', 'string', 'max:20'],
            'include_in_lzkosz' => ['boolean'],
            'lzkosz_round' => ['nullable', 'string', Rule::requiredIf($this->boolean('include_in_lzkosz')), Rule::in([TeamMatch::LZKOSZ_ROUND_ONE, TeamMatch::LZKOSZ_ROUND_TWO])],
            'opponent_name' => ['required', 'string', 'max:255'],
            'match_date' => [
                'required',
                'date',
                Rule::when($this->input('status') === TeamMatch::STATUS_UPCOMING, ['after_or_equal:today']),
            ],
            'match_time' => ['nullable', 'date_format:H:i'],
            'has_time' => ['boolean'],
            'location' => ['required', 'string', 'max:255'],
            'is_home' => ['boolean'],
            'our_score' => [
                'nullable',
                'integer',
                'min:0',
                'max:999',
                Rule::requiredIf($this->input('status') === TeamMatch::STATUS_FINISHED),
            ],
            'opponent_score' => [
                'nullable',
                'integer',
                'min:0',
                'max:999',
                Rule::requiredIf($this->input('status') === TeamMatch::STATUS_FINISHED),
            ],
            'opponent_logo' => ['nullable', 'image', 'max:'.config('media.max_upload_kilobytes')],
            'home_logo' => ['nullable', 'image', 'max:'.config('media.max_upload_kilobytes')],
            'publish_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_ticketed' => ['boolean'],
            'ticket_url' => ['nullable', Rule::requiredIf($this->boolean('is_ticketed')), 'url', 'max:2048'],
        ];
    }
}
