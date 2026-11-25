import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import './smart-navbar.js';

// A [data-disclosure] button shows and hides the element its aria-controls names (the mobile
// navbar and the dropdown menus), which also closes on a click outside of both or on Escape
document.querySelectorAll('[data-disclosure]').forEach((toggle) => {
    const target = document.getElementById(toggle.getAttribute('aria-controls'));
    const isOpen = () => toggle.getAttribute('aria-expanded') === 'true';
    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', open);
        toggle.classList.toggle('show', open);
        target.classList.toggle('show', open);
    };
    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        setOpen(!isOpen());
    });
    document.addEventListener('click', (event) => {
        if (isOpen() && !toggle.contains(event.target) && !target.contains(event.target)) {
            setOpen(false);
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            setOpen(false);
            // don't leave the focus in what just closed
            if (target.contains(document.activeElement)) {
                toggle.focus();
            }
        }
    });
});

// remember the picked theme, or forget it when it matches the OS so the site follows the OS again
document.querySelector('.theme-toggle').addEventListener('click', () => {
    const root = document.documentElement;
    const theme = root.dataset.bsTheme === 'dark' ? 'light' : 'dark';
    const system = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    try {
        if (theme === system) {
            localStorage.removeItem('theme');
        } else {
            localStorage.setItem('theme', theme);
        }
    } catch {}
    root.dataset.bsTheme = theme;
});
