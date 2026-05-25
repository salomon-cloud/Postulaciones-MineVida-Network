@props(['href', 'active' => false, 'badge' => null, 'icon' => null])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'lumoryx-nav-link '.($active ? 'lumoryx-nav-link-active' : '')]) }}>
    <span class="flex min-w-0 items-center gap-3">
        @if ($icon)
            <span class="lumoryx-nav-icon">{{ $icon }}</span>
        @endif
        <span class="min-w-0 truncate">{{ $slot }}</span>
    </span>
    @if ($badge)
        <span class="lumoryx-nav-badge">{{ $badge }}</span>
    @endif
</a>
