@props(['open' => 'modal'])

<div class="lumoryx-modal-backdrop" x-show="{{ $open }}" x-cloak>
    <div {{ $attributes->merge(['class' => 'lumoryx-modal']) }}>
        {{ $slot }}
    </div>
</div>
