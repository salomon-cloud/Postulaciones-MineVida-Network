import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

function initApplicationWizard(form) {
    const sections = Array.from(form.querySelectorAll('[data-step-section]'));
    const buttons = Array.from(form.querySelectorAll('[data-step-button]'));
    const numbers = Array.from(form.querySelectorAll('[data-step-number]'));
    const titles = Array.from(form.querySelectorAll('[data-step-title]'));
    let currentStep = Number(form.dataset.initialStep || 0);
    let maxUnlockedStep = currentStep;

    const parseStep = (element, attribute) => Number(element.dataset[attribute]);

    const setClasses = (element, classes, enabled) => {
        classes.split(' ').forEach((className) => {
            if (className) {
                element.classList.toggle(className, enabled);
            }
        });
    };

    const update = () => {
        sections.forEach((section) => {
            section.classList.toggle('hidden', parseStep(section, 'stepSection') !== currentStep);
        });

        buttons.forEach((button) => {
            const step = parseStep(button, 'stepButton');
            const isCurrent = step === currentStep;
            const isDone = step < currentStep;

            button.disabled = step > maxUnlockedStep;
            setClasses(button, 'border-amber-300/30 bg-amber-300/10 opacity-70', false);
            setClasses(button, 'border-emerald-300/20 bg-emerald-300/10 border-white/10 bg-white/[.025]', false);
            setClasses(button, 'border-amber-300/30 bg-amber-300/10', isCurrent);
            setClasses(button, 'border-emerald-300/20 bg-emerald-300/10', isDone);
            setClasses(button, 'border-white/10 bg-white/[.025] opacity-70', !isCurrent && !isDone);
        });

        numbers.forEach((number) => {
            const step = parseStep(number, 'stepNumber');
            const isCurrent = step === currentStep;
            const isDone = step < currentStep;

            setClasses(number, 'border-amber-300/30 bg-amber-300/15 text-amber-100 border-emerald-300/20 bg-emerald-300/15 text-emerald-100 border-white/10 bg-graphite-950/50 text-slate-400', false);
            setClasses(number, 'border-amber-300/30 bg-amber-300/15 text-amber-100', isCurrent);
            setClasses(number, 'border-emerald-300/20 bg-emerald-300/15 text-emerald-100', isDone);
            setClasses(number, 'border-white/10 bg-graphite-950/50 text-slate-400', !isCurrent && !isDone);
        });

        titles.forEach((title) => {
            const step = parseStep(title, 'stepTitle');
            title.classList.toggle('text-white', step <= currentStep);
            title.classList.toggle('text-slate-400', step > currentStep);
        });
    };

    const currentSection = () => sections.find((section) => parseStep(section, 'stepSection') === currentStep);

    const validateCurrentStep = () => {
        const section = currentSection();
        const controls = Array.from(section?.querySelectorAll('input, textarea, select') || [])
            .filter((control) => control.willValidate);

        for (const control of controls) {
            if (!control.checkValidity()) {
                control.reportValidity();
                return false;
            }
        }

        return true;
    };

    const goTo = (step) => {
        currentStep = Math.max(0, Math.min(step, sections.length - 1));
        maxUnlockedStep = Math.max(maxUnlockedStep, currentStep);
        update();
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    form.addEventListener('click', (event) => {
        const button = event.target.closest('[data-step-button], [data-step-next], [data-step-back]');

        if (!button || !form.contains(button)) {
            return;
        }

        if (button.matches('[data-step-button]')) {
            const step = parseStep(button, 'stepButton');

            if (step <= maxUnlockedStep) {
                goTo(step);
            }
        }

        if (button.matches('[data-step-next]') && validateCurrentStep()) {
            goTo(currentStep + 1);
        }

        if (button.matches('[data-step-back]')) {
            goTo(currentStep - 1);
        }
    });

    form.addEventListener('submit', (event) => {
        if (currentStep < sections.length - 1) {
            event.preventDefault();

            if (validateCurrentStep()) {
                goTo(currentStep + 1);
            }
        }
    });

    update();
}

document.querySelectorAll('[data-application-wizard]').forEach(initApplicationWizard);

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-text]');

    if (!button) {
        return;
    }

    const text = button.dataset.copyText || '';
    const originalHtml = button.innerHTML;

    try {
        await navigator.clipboard.writeText(text);
        button.textContent = 'Copiado';
        window.setTimeout(() => {
            button.innerHTML = originalHtml;
        }, 1600);
    } catch (error) {
        button.textContent = text;
        window.setTimeout(() => {
            button.innerHTML = originalHtml;
        }, 2200);
    }
});

function initConfirmDialog() {
    const dialog = document.getElementById('lumoryx-confirm-dialog');

    if (!dialog) {
        return;
    }

    const card = dialog.querySelector('.lumoryx-confirm-card');
    const title = dialog.querySelector('[data-confirm-title]');
    const message = dialog.querySelector('[data-confirm-message]');
    const accept = dialog.querySelector('[data-confirm-accept]');
    const cancel = dialog.querySelector('[data-confirm-cancel]');
    const icon = dialog.querySelector('[data-confirm-icon]');
    let pendingForm = null;
    let pendingSubmitter = null;

    const toneClasses = ['is-danger', 'is-success', 'is-warning'];

    const close = () => {
        dialog.hidden = true;
        dialog.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        pendingForm = null;
        pendingSubmitter = null;
    };

    const open = (form) => {
        const tone = form.dataset.confirmTone || 'danger';

        title.textContent = form.dataset.confirmTitle || 'Confirmar accion';
        message.textContent = form.dataset.confirmMessage || 'Esta accion requiere confirmacion.';
        accept.textContent = form.dataset.confirmConfirmText || 'Confirmar';
        cancel.textContent = form.dataset.confirmCancelText || 'Cancelar';
        icon.textContent = tone === 'success' ? 'OK' : '!';

        card.classList.remove(...toneClasses);
        card.classList.add(`is-${tone}`);

        dialog.hidden = false;
        dialog.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        cancel.focus();
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.matches('[data-confirm]') || form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();
        pendingForm = form;
        pendingSubmitter = event.submitter || null;
        open(form);
    });

    accept.addEventListener('click', () => {
        if (!pendingForm) {
            close();
            return;
        }

        const form = pendingForm;
        const submitter = pendingSubmitter;
        form.dataset.confirmed = 'true';
        close();

        if (form.requestSubmit) {
            form.requestSubmit(submitter || undefined);
        } else {
            form.submit();
        }

        window.setTimeout(() => {
            delete form.dataset.confirmed;
        }, 0);
    });

    cancel.addEventListener('click', close);

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!dialog.hidden && event.key === 'Escape') {
            close();
        }
    });
}

initConfirmDialog();

function initOfflineExperience() {
    const appName = window.lumoryxConfig?.appName || 'el sistema';

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }

    const overlay = document.createElement('div');
    overlay.className = 'lumoryx-offline-overlay';
    overlay.hidden = true;
    overlay.setAttribute('role', 'alert');
    overlay.setAttribute('aria-live', 'assertive');
    overlay.innerHTML = `
        <div class="lumoryx-offline-card">
            <span class="lumoryx-offline-kicker"><span></span> Sin conexion</span>
            <h2>No hay internet</h2>
            <p>El sistema no puede comunicarse con ${appName}. Revisa tu conexion y vuelve a intentarlo.</p>
            <div class="lumoryx-offline-actions">
                <button class="lumoryx-button-primary" type="button" data-offline-retry>Reintentar</button>
                <a class="lumoryx-button-secondary" href="/offline">Ver pantalla offline</a>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    const setOfflineState = () => {
        const offline = navigator.onLine === false;
        overlay.hidden = !offline;
        document.body.classList.toggle('lumoryx-is-offline', offline);
    };

    overlay.querySelector('[data-offline-retry]')?.addEventListener('click', () => {
        if (navigator.onLine) {
            window.location.reload();
            return;
        }

        setOfflineState();
    });

    window.addEventListener('online', setOfflineState);
    window.addEventListener('offline', setOfflineState);
    setOfflineState();
}

initOfflineExperience();
