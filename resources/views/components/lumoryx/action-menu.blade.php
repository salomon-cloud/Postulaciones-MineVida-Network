@props(['label' => 'Mas acciones'])

<div
    x-data="{
        open: false,
        menuStyle: '',
        position() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const top = rect.bottom + 6;
            const right = window.innerWidth - rect.right;
            this.menuStyle = 'top:' + top + 'px; right:' + right + 'px;';
        },
        toggle() {
            if (this.open) { this.open = false; return; }
            this.position();
            this.open = true;
        },
    }"
    @keydown.escape.window="open = false"
    @scroll.window="open = false"
    @resize.window="open = false"
    class="inline-block"
>
    <button
        type="button"
        x-ref="trigger"
        class="lumoryx-action-menu-trigger"
        @click="toggle()"
        :aria-expanded="open ? 'true' : 'false'"
        aria-haspopup="true"
        title="{{ $label }}"
    >
        <span class="sr-only">{{ $label }}</span>
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="5" r="1.7" /><circle cx="12" cy="12" r="1.7" /><circle cx="12" cy="19" r="1.7" /></svg>
    </button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-120"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="lumoryx-action-menu"
            :style="menuStyle"
            @click="open = false"
        >
            {{ $slot }}
        </div>
    </template>
</div>
