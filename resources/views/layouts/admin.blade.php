@extends('layouts.app')

@section('hide_footer', 'true')

@section('content')
<div class="grid min-h-screen bg-slate-100 text-slate-950 lg:grid-cols-[18rem_1fr]">
    @include('partials.admin-sidebar')
    <div class="min-w-0">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-slate-950 px-4 py-4 text-white shadow-sm sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <nav aria-label="Ścieżka nawigacji" class="flex flex-wrap items-center gap-3 text-sm">
                    <a href="{{ route('profile.edit') }}" class="text-slate-200 hover:text-yellow-400">Panel administratora</a>
                    <span aria-hidden="true" class="text-slate-400">/</span>
                    <span class="font-bold">@yield('title', 'Panel administratora')</span>
                </nav>
                <a href="{{ route('profile.edit', ['section' => 'account']) }}" class="flex items-center gap-2 text-sm hover:text-yellow-400">
                    <i data-lucide="user-round" class="h-4 w-4"></i>{{ auth()->user()->name }}
                </a>
            </div>
        </header>
        <div class="min-w-0 p-4 sm:p-6 lg:p-8">
            @yield('admin-content')
        </div>
    </div>
</div>
@endsection
