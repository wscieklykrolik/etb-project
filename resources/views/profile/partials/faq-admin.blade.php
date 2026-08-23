<section id="faq" class="{{ $activeSection === 'faq' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm" x-data="{ sectionQuery: '' }">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-black">Pytania i odpowiedzi</h2>
            <p class="text-sm text-slate-600">Najczęstsze pytania widoczne na stronie głównej pod akademią.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <input type="search" x-model="sectionQuery" placeholder="Szukaj w pytaniach" class="rounded-lg border-slate-300 text-sm">
            <button type="button" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300" @click="openModal = 'faq-create'">Dodaj pytanie</button>
        </div>
    </div>

    <div class="admin-scroll-list space-y-3">
        @forelse ($faqQuestions as $question)
            <article data-admin-search x-show="!sectionQuery || $el.textContent.toLowerCase().includes(sectionQuery.toLowerCase())" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-black text-yellow-400">#{{ $question->sort_order }}</span>
                            <span class="text-xs font-bold uppercase {{ $question->is_published ? 'text-emerald-700' : 'text-slate-500' }}">{{ $question->is_published ? 'Widoczne' : 'Ukryte' }}</span>
                            @if ($question->published_at && $question->published_at->isFuture())
                                <span class="text-xs font-bold uppercase text-yellow-700">Zaplanowane</span>
                            @endif
                        </div>
                        <h3 class="mt-2 text-lg font-black">{{ $question->question }}</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $question->answer }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = 'faq-edit-{{ $question->id }}'">Edytuj</button>
                        <form method="POST" action="{{ route('admin.faq.destroy', $question) }}" onsubmit="return confirm('Usunąć to pytanie?')">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Brak pytań. Dodaj pierwsze pytanie, aby sekcja pojawiła się na stronie głównej.</p>
        @endforelse
    </div>
</section>

<div x-show="openModal === 'faq-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
        <h4 class="mb-4 text-lg font-black">Dodaj pytanie</h4>
        <form method="POST" action="{{ route('admin.faq.store') }}" class="space-y-4">
            @csrf
            @include('profile.partials.faq-question-form-fields')
            <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz pytanie</button></div>
        </form>
    </div>
</div>

@foreach ($faqQuestions as $question)
    <div x-show="openModal === 'faq-edit-{{ $question->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
            <h4 class="mb-4 text-lg font-black">Edytuj pytanie</h4>
            <form method="POST" action="{{ route('admin.faq.update', $question) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('profile.partials.faq-question-form-fields', ['question' => $question])
                <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button></div>
            </form>
        </div>
    </div>
@endforeach
