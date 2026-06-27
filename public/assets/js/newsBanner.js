var BANNER_TEXT = 'NOTÍCIAS';

window._configReady.then(function (cfg) {
    BANNER_TEXT = cfg.texto_banner || 'NOTÍCIAS';
});

function _buildBannerChars() {
    var textEl = document.querySelector('.visible-text');
    textEl.innerHTML = '';
    var frag = new DocumentFragment();
    [...BANNER_TEXT].forEach(function (char) {
        var span = document.createElement('span');
        span.innerText = char === ' ' ? ' ' : char;
        frag.appendChild(span);
    });
    textEl.appendChild(frag);
}

function _animateBanner(onComplete) {
    var characterEls = document.querySelectorAll('.visible-text span');

    anime.set('.banner',     { scaleX: 0 });
    anime.set('.muted-text', { opacity: 0 });
    anime.set(characterEls,  { opacity: 0, translateY: 30, translateX: -10 });

    var tl = anime.timeline({
        easing: 'easeOutExpo',
        duration: 400,
        complete: onComplete,
    });

    tl.add({
        targets: '.banner',
        scaleX: [0, 1],
        duration: 800,
        easing: 'easeOutCubic',
    })
    .add({
        targets: '.muted-text',
        opacity: [0, 1],
    })
    .add({
        targets: characterEls,
        delay: anime.stagger(100),
        translateY: [30, 0],
        translateX: [-10, 0],
        opacity: [0, 1],
        easing: 'spring(1, 80, 10, 0)',
    });

    return tl;
}

function showNewsBanner(onComplete) {
    var wrapper = document.getElementById('news-banner');
    _buildBannerChars();
    wrapper.style.display = 'flex';
    _animateBanner(onComplete);
}

function hideNewsBanner() {
    document.getElementById('news-banner').style.display = 'none';
}
