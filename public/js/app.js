document.addEventListener('DOMContentLoaded', () => {


    /* ---------- Show / Hide password buttons ---------- */
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        const input = button.parentElement.querySelector('input');
        if (!input) return;

        button.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.textContent = show ? 'Hide' : 'Show';
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });

    /* ---------- Success message: auto-hide + close button ---------- */
    document.querySelectorAll('.toast').forEach((toast) => {
        const hide = () => {
            toast.classList.add('toast--hide');
            setTimeout(() => toast.remove(), 450);
        };
        setTimeout(hide, 5000);
        toast.querySelector('.toast-close')?.addEventListener('click', hide);
    });

    /* ---------- Filters: auto-submit selects, debounce the search box ---------- */
    const filterForm = document.querySelector('[data-filters]');
    if (filterForm) {
        filterForm.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => filterForm.submit());
        });

        const search = filterForm.querySelector('input[type="search"]');
        if (search) {
            let timer;
            search.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => filterForm.submit(), 600);
            });

            // Keep typing where the user left off after the page reloads
            if (search.value) {
                search.focus();
                search.setSelectionRange(search.value.length, search.value.length);
            }
        }
    }

    /* ---------- Delete confirmation dialog ---------- */
    const dialog = document.getElementById('confirmDialog');
    let pendingForm = null;

    if (dialog) {
        const text = dialog.querySelector('[data-dialog-text]');

        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === '1') return;
                event.preventDefault();
                pendingForm = form;
                text.textContent = form.dataset.confirm;
                dialog.showModal();
            });
        });

        dialog.querySelector('[data-cancel]').addEventListener('click', () => {
            pendingForm = null;
            dialog.close();
        });

        dialog.querySelector('[data-ok]').addEventListener('click', () => {
            if (pendingForm) {
                pendingForm.dataset.confirmed = '1';
                pendingForm.submit();
            }
        });

        // Click on the dimmed backdrop closes the dialog
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
    }

    /* ---------- Character counter for the description ---------- */
    document.querySelectorAll('textarea[data-counter]').forEach((area) => {
        const output = document.querySelector(`[data-counter-for="${area.id}"]`);
        if (!output) return;
        const update = () => { output.textContent = area.value.length; };
        area.addEventListener('input', update);
        update();
    });

    /* ---------- Stop double submits on create / edit forms ---------- */
    document.querySelectorAll('form[data-submit-lock]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.textContent = 'Saving...';
            }
        });
    });
});

/* ---------- Update: photo preview, print button, month picker ---------- */
document.addEventListener('DOMContentLoaded', () => {
    const MAX_BYTES = 2 * 1024 * 1024;

    document.querySelectorAll('[data-photo-input]').forEach((input) => {
        const preview = document.querySelector('[data-photo-preview]');

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (preview) preview.hidden = true;
            if (!file) return;

            if (file.size > MAX_BYTES) {
                alert('That photo is larger than 2 MB. Please choose a smaller one.');
                input.value = '';
                return;
            }
            if (preview) {
                preview.src = URL.createObjectURL(file);
                preview.hidden = false;
            }
        });
    });

    document.querySelectorAll('[data-print]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });

    document.querySelectorAll('[data-autosubmit]').forEach((el) => {
        el.addEventListener('change', () => el.form.submit());
    });
});
