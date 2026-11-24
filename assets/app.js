import { Collapse, Tooltip } from 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import './smart-navbar.js';

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
    new Tooltip(element);
});

// close out the open navbar in mobile mode when we click outside of it
var navbar = document.getElementById('navbar');

function hideNavbar(event) {
    if (!navbar.contains(event.target)) {
        Collapse.getOrCreateInstance(navbar).hide();
    }
}

navbar.addEventListener('shown.bs.collapse', function () {
    document.addEventListener('click', hideNavbar);
});
navbar.addEventListener('hidden.bs.collapse', function () {
    document.removeEventListener('click', hideNavbar);
});
