import { Tab } from 'bootstrap';

function showOS(os) {
    location.hash = os;
    if (os.indexOf('linux') !== -1) {
        if (os === '#linux') {
            // Default linux distro shown is Debian
            os = '#linux-debian';
        }
        var tab = document.querySelector(os + '-button');
        if (tab) {
            Tab.getOrCreateInstance(tab).show();
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
