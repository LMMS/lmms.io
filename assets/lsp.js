// Keep the account panel collapsed on small screens
window.addEventListener('load', function () {
    var loginCollapse = document.getElementById('login-collapse');
    if (document.documentElement.clientWidth >= 962) {
        loginCollapse.classList.add('show');
    } else if (!document.activeElement.matches('#login, #password')) {
        loginCollapse.classList.remove('show');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    /*
     * Redirect page if alert contains a data-redirect tag
     */
    document.querySelectorAll('.alert').forEach(function (alert) {
        var counter = document.querySelector('.redirect-counter');
        var timeout = counter ? parseInt(counter.textContent) * 1000 : 5000;
        if (alert.dataset.redirect) {
            window.setTimeout(function () {
                window.location = alert.dataset.redirect;
            }, timeout);
            countDown(false);
        }
    });

    /*
     * Focus to comment text-area if it is on the screen
     */
    commentFocus();

    var loginLink = document.getElementById('login-focus');
    if (loginLink) {
        loginLink.addEventListener('click', function (event) {
            event.preventDefault();
            loginFocus();
        });
    }
});

function countDown(decrement) {
    var counter = document.querySelector('.redirect-counter');
    if (counter) {
        if (decrement) {
            counter.textContent = parseInt(counter.textContent) - 1;
        }
        if (counter.textContent == '-1') {
            counter.remove();
            return;
        } else {
            window.setTimeout(function () {
                countDown(true);
            }, 1000);
            return;
        }
    }
}

function commentFocus() {
    var comment = document.getElementById('comment');
    if (comment) {
        comment.focus();
    }
}

function loginFocus() {
    document.getElementById('login-collapse').classList.add('show');
    document.getElementById('login').focus();
    blink('#login');
}

function blink(selector) {
    document.querySelector(selector).animate([{ opacity: 1 }, { opacity: 0 }], {
        duration: 100,
        iterations: 6,
        direction: 'alternate',
    });
}

/*
 * Bootstrap file button listener
 */
document.addEventListener('change', function (event) {
    if (!event.target.matches('.btn-file input[type=file]')) {
        return;
    }
    var label = event.target.value.replace(/\\/g, '/').replace(/.*\//, '');
    var selected = document.getElementById('file-selected');
    selected.textContent = label ? label : 'No file selected';
    selected.className = label ? 'text-primary' : 'text-danger';
    blink('#file-selected');
});
