@php($question = $question ?? null)

<div>
    <label class="text-sm font-bold text-slate-700">Pytanie</label>
    <input name="question" required value="{{ old('question', $question?->question) }}" placeholder="Jak zapisać dziecko do akademii ETB?" class="mt-1 w-full rounded-lg border-slate-300">
</div>

<div>
    <label class="text-sm font-bold text-slate-700">Odpowiedź</label>
    <textarea name="answer" required rows="6" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Napisz konkretną odpowiedź widoczną na stronie głównej.">{{ old('answer', $question?->answer) }}</textarea>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="text-sm font-bold text-slate-700">Kolejność</label>
        <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $question?->sort_order ?? 0) }}" class="mt-1 w-full rounded-lg border-slate-300">
    </div>
    <div>
        <label class="text-sm font-bold text-slate-700">Data publikacji</label>
        <input name="published_at" type="datetime-local" value="{{ old('published_at', $question?->published_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-slate-300">
    </div>
</div>

<label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
    <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-yellow-500" @checked(old('is_published', $question?->is_published ?? true))>
    Widoczne na stronie głównej
</label>
