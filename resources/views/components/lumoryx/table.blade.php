<div {{ $attributes->merge(['class' => 'lumoryx-panel overflow-hidden']) }}>
    <div class="overflow-x-auto">
        <table class="lumoryx-table">
            {{ $slot }}
        </table>
    </div>
</div>
