<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-slate-800">My portfolio</h2></x-slot>
    <div class="min-h-[calc(100vh-65px)] bg-slate-950 py-12"><div class="max-w-5xl mx-auto px-6">
        @if(session('status'))<div class="mb-6 rounded-xl bg-indigo-50 px-5 py-4 text-indigo-800">{{ session('status') }}</div>@endif
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-8 text-white">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div><p class="text-sm uppercase tracking-widest text-indigo-400">DevFolio</p><h1 class="mt-2 text-3xl font-bold">Share your work beautifully.</h1><p class="mt-2 text-slate-400">Build a polished, public portfolio from your real GitHub projects.</p></div>
                @if($portfolio)<span class="rounded-full px-3 py-1 text-sm {{ $portfolio->is_published ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">{{ $portfolio->is_published ? 'Published' : 'Draft' }}</span>@endif
            </div>
            @if($portfolio)
                <div class="mt-8 rounded-xl border border-slate-700 bg-slate-800/60 p-5"><p class="text-slate-400">Live address</p><a class="mt-1 block text-indigo-300 hover:text-indigo-200" href="{{ route('portfolio.public', $portfolio->subdomain) }}">/p/{{ $portfolio->subdomain }}</a></div>
                <p class="mt-4 text-sm text-slate-400">AI content: <span class="font-semibold {{ $portfolio->ai_status === 'failed' ? 'text-rose-300' : 'text-indigo-300' }}">{{ ucfirst($portfolio->ai_status) }}</span></p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('portfolio.edit', $portfolio) }}" class="rounded-lg bg-indigo-500 px-5 py-3 font-semibold hover:bg-indigo-400">Edit portfolio</a>
                    <a href="{{ route('portfolio.preview', $portfolio) }}" class="rounded-lg border border-slate-600 px-5 py-3 font-semibold hover:bg-slate-800">Preview</a>
                    @if($portfolio->is_published)
                        <form method="POST" action="{{ route('portfolio.unpublish', $portfolio) }}">
                            @csrf
                            <button class="rounded-lg border border-rose-300 px-5 py-3 font-semibold text-rose-200 hover:bg-rose-500/10">Unpublish</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('portfolio.publish', $portfolio) }}">
                            @csrf
                            <button class="rounded-lg bg-emerald-500 px-5 py-3 font-semibold text-white hover:bg-emerald-400">Publish portfolio</button>
                        </form>
                    @endif
                </div>
            @else
                <a href="{{ route('portfolio.create') }}" class="mt-8 inline-flex rounded-lg bg-indigo-500 px-5 py-3 font-semibold hover:bg-indigo-400">Create your portfolio</a>
            @endif
        </div>
    </div></div>
</x-app-layout>
