@extends('auth.admin.layout')

@php
    $activeSection = 'stats';
@endphp

@section('content')
    <header class="mb-4">
        <h2 class="m-0 text-admin-ink text-xl font-bold">Team Stats</h2>
        <p class="mt-[0.35rem] text-admin-muted">Live snapshot from the admin stats endpoint.</p>
    </header>

    <div id="stats-container" class="grid grid-cols-2 gap-[0.9rem]"></div>

    <script type="text/template" id="stats-template">
        <article class="border border-[#dde4ea] rounded-[0.9rem] bg-[#fbfdff] p-4 flex items-center gap-3">
            <div class="w-[0.8rem] h-[0.8rem] rounded-full shrink-0 {dotClass}"></div>
            <div>
                <p class="m-0 text-[0.76rem] text-[#66717a] uppercase tracking-[0.02em]">{title}</p>
                <h3 class="mt-[0.2rem] text-[1.3rem] text-[#162127]">{stat}</h3>
            </div>
        </article>
    </script>
@endsection
