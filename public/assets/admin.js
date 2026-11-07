(function () {
    'use strict';
    // Demande une confirmation avant les actions destructrices (formulaires marqués data-confirm).
    document.addEventListener('submit', function (e) {
        var message = e.target && e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (message && !window.confirm(message)) { e.preventDefault(); }
    });
}());
