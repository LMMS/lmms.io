import './styles/lsp.css';

/*
 * Redirect page if alert contains a data-redirect tag
 */
document.querySelectorAll('[data-redirect]').forEach((alert) => {
    if (!alert.dataset.redirect) {
        return;
    }
    const counter = document.querySelector('.redirect-counter');
    let seconds = counter ? parseInt(counter.textContent) : 5;
    const timer = window.setInterval(() => {
        seconds--;
        if (counter) {
            counter.textContent = Math.max(seconds, 0);
        }
        if (seconds <= 0) {
            window.clearInterval(timer);
            window.location = alert.dataset.redirect;
        }
    }, 1000);
});

/*
 * Close the user menu on a click outside of it or on Escape
 */
const menu = document.querySelector('.lsp-menu');
if (menu) {
    document.addEventListener('click', (event) => {
        if (!menu.contains(event.target)) {
            menu.open = false;
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menu.open) {
            menu.open = false;
            menu.querySelector('summary').focus();
        }
    });
}

/*
 * Show the picked file's name in the upload drop zone
 */
document.querySelectorAll('.lsp-drop').forEach((zone) => {
    const input = zone.querySelector('input[type=file]');
    const title = zone.querySelector('.lsp-drop-title');
    const placeholder = title.textContent;
    input.addEventListener('change', () => {
        const [file] = input.files;
        title.textContent = file ? file.name : placeholder;
        zone.classList.toggle('selected', !!file);
    });
    input.addEventListener('dragenter', () => zone.classList.add('dragover'));
    for (const type of ['dragleave', 'drop']) {
        input.addEventListener(type, () => zone.classList.remove('dragover'));
    }
});

/*
 * Apply a filter checkbox as soon as it's toggled
 */
document.addEventListener('change', (event) => {
    if (event.target.matches('[data-autosubmit]')) {
        event.target.form.requestSubmit();
    }
});

/*
 * "/" jumps to the search, unless the user is already typing somewhere
 */
const searchInput = document.querySelector('.lsp-search input[name=search]');
if (searchInput) {
    document.documentElement.classList.add('lsp-search-shortcut');
    document.addEventListener('keydown', (event) => {
        if (event.key !== '/' || event.ctrlKey || event.metaKey || event.altKey) {
            return;
        }
        if (event.target.closest('input, textarea, select, [contenteditable]')) {
            return;
        }
        event.preventDefault();
        searchInput.focus();
        searchInput.select();
    });
}

/*
 * Cancel on the comment box clears it (the form reset) and closes it again
 */
document.querySelectorAll('.lsp-reply').forEach((form) => {
    form.addEventListener('reset', () => document.activeElement.blur());
});
