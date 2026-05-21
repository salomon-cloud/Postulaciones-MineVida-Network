@props(['title', 'body', 'time' => null, 'tone' => 'purple'])

<div {{ $attributes->merge(['class' => 'flex min-w-0 gap-4 rounded-lg border border-white/10 bg-white/[.035] p-4']) }}>
    <div class="lumoryx-icon-tile h-11 w-11 text-amber-200">!</div>
    <div class="min-w-0 flex-1">
        <p class="truncate font-semibold text-white">{{ $title }}</p>
        <p class="lumoryx-break mt-1 text-sm text-slate-400">{{ $body }}</p>
    </div>
    @if ($time)
        <span class="shrink-0 text-sm text-slate-500">{{ $time }}</span>
    @endif
</div>
