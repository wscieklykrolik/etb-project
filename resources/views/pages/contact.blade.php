@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">ETB Basket</p>
        <h1 class="mt-2 text-4xl font-black text-white">Kontakt</h1>
    </div>

    <div class="space-y-16">
        @include('pages.partials.club-section-content', ['clubSection' => $clubSection, 'sectionId' => 'contact'])

        <section id="marketing" class="scroll-mt-28">
            <div class="mb-6">
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">Kontakt</p>
                <h2 class="mt-2 text-3xl font-black text-white">Marketing</h2>
            </div>

            <div class="max-w-4xl rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                <p class="text-base leading-8 text-zinc-100 sm:text-lg">W sprawach marketingu, współpracy medialnej i materiałów promocyjnych napisz do nas.</p>
                <a href="mailto:media@etb-lodz.pl" class="mt-4 inline-flex items-center gap-2 text-lg font-black text-yellow-400 transition hover:text-yellow-300">media@etb-lodz.pl</a>
            </div>
        </section>
    </div>
</section>
@endsection
