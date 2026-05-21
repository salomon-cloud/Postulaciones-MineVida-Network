<div id="lumoryx-confirm-dialog" class="lumoryx-confirm-backdrop" hidden aria-hidden="true">
    <div class="lumoryx-confirm-card" role="dialog" aria-modal="true" aria-labelledby="lumoryx-confirm-title" aria-describedby="lumoryx-confirm-message">
        <div class="flex items-start gap-4">
            <span class="lumoryx-confirm-icon" data-confirm-icon>!</span>
            <div class="min-w-0 flex-1">
                <p class="lumoryx-kicker" data-confirm-eyebrow>Confirmar accion</p>
                <h2 id="lumoryx-confirm-title" class="mt-2 text-2xl font-black text-white" data-confirm-title>Confirmar</h2>
                <p id="lumoryx-confirm-message" class="mt-3 text-sm leading-6 text-slate-400" data-confirm-message>Esta accion requiere confirmacion.</p>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button class="lumoryx-button-secondary" type="button" data-confirm-cancel>Cancelar</button>
            <button class="lumoryx-button-danger" type="button" data-confirm-accept>Confirmar</button>
        </div>
    </div>
</div>
