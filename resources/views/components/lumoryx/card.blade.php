@props(['glow' => false])

<div {{ $attributes->merge(['class' => ($glow ? 'lumoryx-panel-glow' : 'lumoryx-panel')]) }}>
    {{ $slot }}
</div>
