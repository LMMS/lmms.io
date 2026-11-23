$(function () {
    $('[data-toggle=\'tooltip\']').tooltip();
});

$(window).bind('load', function () {
    if ($(this).width() < 962) {
        collapse_in('#caret');
    } else {
        collapse_out('#caret');
    }
});

function collapse_out(item_id) {
    $('.collapse')
        .not('.navbar-collapse')
        .addClass('in');
    $(item_id).removeClass('fa-caret-right');
    $(item_id).addClass('fa-caret-down');
}

function collapse_in(item_id) {
    if ($('#login').is(':focus') || $('#password').is(':focus')) {
        return;
    }
    $('.collapse')
        .not('.navbar-collapse')
        .removeClass('in');
    $(item_id).removeClass('fa-caret-down');
    $(item_id).addClass('fa-caret-right');
}

$('.collapse')
    .not('.navbar-collapse')
    .on('shown.bs.collapse', function () {
        collapse_out('#caret');
    });
$('.collapse')
    .not('.navbar-collapse')
    .on('hidden.bs.collapse', function () {
        collapse_in('#caret');
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
    document.getElementById('login-collapse').classList.add('in');
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
