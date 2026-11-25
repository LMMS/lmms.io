var tabs = Array.from(document.querySelectorAll('#linux-tabs [role="tab"]'));

function showTab(tab) {
    tabs.forEach(function (other) {
        var selected = other === tab;
        other.classList.toggle('active', selected);
        other.setAttribute('aria-selected', selected);
        other.tabIndex = selected ? 0 : -1;
        document.getElementById(other.getAttribute('aria-controls')).classList.toggle('active', selected);
    });
}

// arrow keys move between the tabs, as in any tab list
var tabKeys = { ArrowLeft: -1, ArrowUp: -1, ArrowRight: 1, ArrowDown: 1 };

tabs.forEach(function (tab, i) {
    tab.addEventListener('click', function (event) {
        event.preventDefault();
        showTab(tab);
    });
    tab.addEventListener('keydown', function (event) {
        if (!(event.key in tabKeys)) {
            return;
        }
        event.preventDefault();
        var next = tabs[(i + tabKeys[event.key] + tabs.length) % tabs.length];
        showTab(next);
        next.focus();
    });
});

function showOS(os) {
    location.hash = os;
    if (os.indexOf('linux') !== -1) {
        if (os === '#linux') {
            // Default linux distro shown is Debian
            os = '#linux-debian';
        }
        var tab = document.querySelector(os + '-button');
        if (tab) {
            showTab(tab);
        }
        os = '#linux';
    }

    document.querySelectorAll('#windows-div, #linux-div, #mac-div').forEach(function (div) {
        div.hidden = true;
    });
    document.querySelector(os + '-div').hidden = false;
    document.querySelector(os + '-button').checked = true;
}

function autoSelect() {
    if (navigator.userAgent.indexOf('Mac') !== -1)
        showOS('#mac');
    else if (navigator.userAgent.indexOf('X11') !== -1)
        showOS('#linux');
    else if (navigator.userAgent.indexOf('Linux') !== -1)
        showOS('#linux');
    else showOS('#windows');
}

document.querySelectorAll('input[name="os"]').forEach(function (input) {
    input.addEventListener('change', function () {
        showOS(input.value);
    });
});

if (location.hash) {
    try {
        showOS(location.hash);
    } catch (err) {
        autoSelect();
    }
} else {
    autoSelect();
}
