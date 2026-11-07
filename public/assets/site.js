(function () {
    'use strict';

    // Menu mobile
    var burger = document.querySelector('[data-burger]');
    var nav = document.querySelector('[data-nav]');
    if (burger && nav) {
        burger.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            burger.setAttribute('aria-expanded', String(open));
        });
    }

    // Vidéos YouTube/Vimeo : rien n'est chargé chez un tiers avant le clic.
    document.querySelectorAll('[data-embed]').forEach(function (button) {
        button.addEventListener('click', function () {
            var url = button.getAttribute('data-embed');
            var frame = document.createElement('iframe');
            frame.src = url + (url.indexOf('?') > -1 ? '&' : '?') + 'autoplay=1';
            frame.title = button.getAttribute('aria-label') || 'Vidéo';
            frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
            frame.allowFullscreen = true;
            frame.referrerPolicy = 'strict-origin-when-cross-origin';
            button.replaceWith(frame);
            frame.focus();
        });
    });

    // Visionneuse d'images
    var dialog = document.getElementById('lightbox');
    var items = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox]'));
    if (!dialog || !items.length || typeof dialog.showModal !== 'function') { return; }

    var img = dialog.querySelector('img');
    var caption = dialog.querySelector('figcaption');
    var index = 0;

    function show(i) {
        index = (i + items.length) % items.length;
        img.src = items[index].getAttribute('data-full');
        var text = items[index].getAttribute('data-caption') || '';
        img.alt = text;
        caption.textContent = text;
    }
    items.forEach(function (item, i) {
        item.addEventListener('click', function () { show(i); dialog.showModal(); });
    });
    dialog.querySelector('.lb-close').addEventListener('click', function () { dialog.close(); });
    dialog.querySelector('.lb-prev').addEventListener('click', function () { show(index - 1); });
    dialog.querySelector('.lb-next').addEventListener('click', function () { show(index + 1); });
    dialog.addEventListener('click', function (e) { if (e.target === dialog) { dialog.close(); } });
    dialog.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') { show(index - 1); }
        if (e.key === 'ArrowRight') { show(index + 1); }
    });
}());
