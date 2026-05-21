@props(['href', 'active' => false, 'badge' => null])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'lumoryx-nav-link '.($active ? 'lumoryx-nav-link-active' : '')]) }}>
    <span class="min-w-0 truncate">{{ $slot }}</span>
    @if ($badge)
        <span class="rounded-md bg-amber-300/15 px-2 py-0.5 text-xs font-bold text-amber-100">{{ $badge }}</span>
    @endif
</a>
