(function () {
    'use strict';

    if (typeof cfmaFilter === 'undefined') return;

    const { feedUrl, perPage } = cfmaFilter;
    let currentPage   = 1;
    let currentArtist = '';
    let currentAlbum  = '';
    let totalPages    = 1;
    let totalResults  = 0;
    let isFetching    = false;

    // --- URL state ---

    function pushState() {
        const params = new URLSearchParams();
        if (currentArtist) params.set('cfma_artist', currentArtist);
        if (currentAlbum)  params.set('cfma_album', currentAlbum);
        if (currentPage > 1) params.set('cfma_page', currentPage);
        const qs = params.toString();
        history.replaceState(null, '', qs ? '?' + qs : window.location.pathname);
    }

    function readState() {
        const params = new URLSearchParams(window.location.search);
        currentArtist = params.get('cfma_artist') || '';
        currentAlbum  = params.get('cfma_album')  || '';
        currentPage   = parseInt(params.get('cfma_page') || '1', 10);

        var artistIds = currentArtist ? currentArtist.split(',') : [];
        document.querySelectorAll('.cfma-artist-cb').forEach(function (cb) {
            cb.checked = artistIds.indexOf(cb.value) !== -1;
        });

        var albumIds = currentAlbum ? currentAlbum.split(',') : [];
        document.querySelectorAll('.cfma-album-cb').forEach(function (cb) {
            cb.checked = albumIds.indexOf(cb.value) !== -1;
        });
    }

    // --- REST API ---

    function buildUrl(page) {
        const params = new URLSearchParams({
            per_page: perPage,
            page:     page,
        });
        if (currentArtist) params.set('cryns_artist', currentArtist);
        if (currentAlbum)  params.set('cryns_album_title', currentAlbum);
        return feedUrl + '?' + params.toString();
    }

    // --- Rendering ---

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderResults(posts) {
        const container = document.getElementById('cfma-results');
        if (!posts || !posts.length) {
            container.innerHTML = '<p class="cfma-no-results">Sorry, no songs found. Please try a different filter.</p>';
            return;
        }

        const items = posts.map(function (post) {
            const title    = post.title ? post.title.rendered : '';
            const link     = escHtml(post.link || '');
            const isAudio  = post.type === 'cryns_audio_file';
            const badgeCls = isAudio ? 'cfma-badge-audio' : 'cfma-badge-post';
            const badgeTxt = isAudio ? '&#9835; Song' : '&#128221; Post';
            const badge    = '<span class="cfma-type-badge ' + badgeCls + '">' + badgeTxt + '</span>';
            const audio    = post.audio_file;
            let playerHtml = '';
            if (audio && audio.url) {
                const mime = escHtml(audio.mime || 'audio/mpeg');
                const src  = escHtml(audio.url);
                playerHtml =
                    '<audio class="wp-audio-shortcode" preload="none" style="width:100%;" controls>' +
                    '<source type="' + mime + '" src="' + src + '">' +
                    '</audio>';
            }
            return '<li class="cfma-song-item">' +
                '<h2 class="cfma-song-title">' + badge + ' <a href="' + link + '">' + title + '</a></h2>' +
                playerHtml +
                '</li>';
        });

        container.innerHTML = '<ul class="cfma-song-list">' + items.join('') + '</ul>';
    }

    function renderPagination() {
        const container = document.getElementById('cfma-pagination');
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        const MAX_VISIBLE = 10;
        let start = Math.max(1, currentPage - Math.floor(MAX_VISIBLE / 2));
        let end   = Math.min(totalPages, start + MAX_VISIBLE - 1);
        if (end - start < MAX_VISIBLE - 1) {
            start = Math.max(1, end - MAX_VISIBLE + 1);
        }

        let html = '<div class="cfma-pagination">';
        if (currentPage > 1) {
            html += '<button class="cfma-page-btn cfma-page-prev" data-page="' + (currentPage - 1) + '">&laquo; Prev</button>';
        }
        for (let i = start; i <= end; i++) {
            const cls = i === currentPage ? 'cfma-page-btn cfma-page-current' : 'cfma-page-btn';
            html += '<button class="' + cls + '" data-page="' + i + '">' + i + '</button>';
        }
        if (currentPage < totalPages) {
            html += '<button class="cfma-page-btn cfma-page-next" data-page="' + (currentPage + 1) + '">Next &raquo;</button>';
        }
        html += '</div>';
        container.innerHTML = html;

        container.querySelectorAll('.cfma-page-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentPage = parseInt(btn.dataset.page, 10);
                fetchSongs({ scrollToResults: true });
            });
        });
    }

    function getCheckedValues(selector) {
        var vals = [];
        document.querySelectorAll(selector + ':checked').forEach(function (cb) {
            vals.push(cb.value);
        });
        return vals.join(',');
    }

    function getCheckedLabels(selector) {
        var labels = [];
        document.querySelectorAll(selector + ':checked').forEach(function (cb) {
            var lbl = cb.closest('label');
            if (lbl) {
                labels.push(lbl.textContent.trim().replace(/\s*\(\d+\)\s*$/, ''));
            }
        });
        return labels;
    }

    function renderSelections() {
        const container = document.getElementById('cfma-selections');
        const chips = [];

        if (currentArtist) {
            const labels = getCheckedLabels('.cfma-artist-cb').map(escHtml).join(', ');
            chips.push(
                '<span class="cfma-chip">Artist: ' + (labels || 'selected') +
                ' <button class="cfma-chip-clear" data-clear="artist" aria-label="Remove artist filter">&times;</button></span>'
            );
        }
        if (currentAlbum) {
            const labels = getCheckedLabels('.cfma-album-cb').map(escHtml).join(', ');
            chips.push(
                '<span class="cfma-chip">Album: ' + (labels || 'selected') +
                ' <button class="cfma-chip-clear" data-clear="album" aria-label="Remove album filter">&times;</button></span>'
            );
        }

        container.innerHTML = chips.join('');

        container.querySelectorAll('.cfma-chip-clear').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.dataset.clear === 'artist') {
                    currentArtist = '';
                    document.querySelectorAll('.cfma-artist-cb').forEach(function (cb) { cb.checked = false; });
                } else {
                    currentAlbum = '';
                    document.querySelectorAll('.cfma-album-cb').forEach(function (cb) { cb.checked = false; });
                }
                currentPage = 1;
                fetchSongs();
            });
        });
    }

    // --- Fetch ---

    function fetchSongs(opts) {
        if (isFetching) return;
        isFetching = true;
        opts = opts || {};

        const resultsEl = document.getElementById('cfma-results');
        const countEl   = document.getElementById('cfma-count');
        resultsEl.innerHTML = '<p class="cfma-loading">Loading…</p>';

        pushState();

        fetch(buildUrl(currentPage))
            .then(function (res) {
                totalResults = parseInt(res.headers.get('X-WP-Total') || '0', 10);
                totalPages   = parseInt(res.headers.get('X-WP-TotalPages') || '1', 10);
                if (countEl) countEl.textContent = totalResults;
                return res.json();
            })
            .then(function (posts) {
                renderResults(posts);
                renderPagination();
                renderSelections();
                if (opts.scrollToResults) {
                    resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            })
            .catch(function () {
                resultsEl.innerHTML = '<p class="cfma-error">Error loading songs. Please try again.</p>';
            })
            .finally(function () {
                isFetching = false;
            });
    }

    // --- Checkbox search filter ---

    function initCheckboxSearch(inputId, groupId) {
        var input = document.getElementById(inputId);
        var group = document.getElementById(groupId);
        if (!input || !group) return;
        input.addEventListener('input', function () {
            var query = input.value.toLowerCase().trim();
            group.querySelectorAll('.cfma-checkbox-label').forEach(function (label) {
                var match = !query || label.textContent.toLowerCase().indexOf(query) !== -1;
                label.style.display = match ? '' : 'none';
            });
        });
    }

    // --- Init ---

    document.addEventListener('DOMContentLoaded', function () {
        if (!document.querySelector('.cfma-artist-cb')) return;

        readState();

        initCheckboxSearch('cfma-artist-search', 'cfma-artist-checkboxes');
        initCheckboxSearch('cfma-album-search', 'cfma-album-checkboxes');

        document.querySelectorAll('.cfma-artist-cb').forEach(function (cb) {
            cb.addEventListener('change', function () {
                currentArtist = getCheckedValues('.cfma-artist-cb');
                currentPage   = 1;
                fetchSongs();
            });
        });

        document.querySelectorAll('.cfma-album-cb').forEach(function (cb) {
            cb.addEventListener('change', function () {
                currentAlbum = getCheckedValues('.cfma-album-cb');
                currentPage  = 1;
                fetchSongs();
            });
        });

        fetchSongs();
    });
}());
