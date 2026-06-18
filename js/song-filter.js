(function () {
    'use strict';

    if (typeof cfmaFilter === 'undefined') return;

    const { restUrl, perPage } = cfmaFilter;
    let currentPage   = 1;
    let currentArtist = '';
    let currentAlbum  = '';
    let totalPages    = 1;
    let totalResults  = 0;
    let isFetching    = false;

    function buildUrl(page) {
        const params = new URLSearchParams({
            per_page: perPage,
            page:     page,
            orderby:  'date',
            order:    'desc',
            _fields:  'id,title,link,audio_file',
        });
        if (currentArtist) params.set('cryns_artist', currentArtist);
        if (currentAlbum)  params.set('cryns_album_title', currentAlbum);
        return restUrl + '?' + params.toString();
    }

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
            const title = post.title ? post.title.rendered : '';
            const link  = escHtml(post.link || '');
            const audio = post.audio_file;
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
                '<h2 class="cfma-song-title"><a href="' + link + '">' + title + '</a></h2>' +
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
                fetchSongs();
                var results = document.getElementById('cfma-results');
                if (results) {
                    window.scrollTo({ top: results.getBoundingClientRect().top + window.pageYOffset - 20, behavior: 'smooth' });
                }
            });
        });
    }

    function getSelectLabel(selectId) {
        var sel = document.getElementById(selectId);
        if (!sel || !sel.value) return '';
        return sel.options[sel.selectedIndex].text;
    }

    function renderSelections() {
        const container = document.getElementById('cfma-selections');
        const chips = [];

        if (currentArtist) {
            const label = escHtml(getSelectLabel('cfma-artist-select'));
            chips.push(
                '<span class="cfma-chip">Artist: ' + label +
                ' <button class="cfma-chip-clear" data-clear="artist" aria-label="Remove artist filter">&times;</button></span>'
            );
        }
        if (currentAlbum) {
            const label = escHtml(getSelectLabel('cfma-album-select'));
            chips.push(
                '<span class="cfma-chip">Album: ' + label +
                ' <button class="cfma-chip-clear" data-clear="album" aria-label="Remove album filter">&times;</button></span>'
            );
        }

        container.innerHTML = chips.join('');

        container.querySelectorAll('.cfma-chip-clear').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.dataset.clear === 'artist') {
                    currentArtist = '';
                    document.getElementById('cfma-artist-select').value = '';
                } else {
                    currentAlbum = '';
                    document.getElementById('cfma-album-select').value = '';
                }
                currentPage = 1;
                fetchSongs();
            });
        });
    }

    function fetchSongs() {
        if (isFetching) return;
        isFetching = true;

        const resultsEl = document.getElementById('cfma-results');
        const countEl   = document.getElementById('cfma-count');
        resultsEl.innerHTML = '<p class="cfma-loading">Loading…</p>';

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
            })
            .catch(function () {
                resultsEl.innerHTML = '<p class="cfma-error">Error loading songs. Please try again.</p>';
            })
            .finally(function () {
                isFetching = false;
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var artistSel = document.getElementById('cfma-artist-select');
        var albumSel  = document.getElementById('cfma-album-select');

        if (!artistSel) return;

        artistSel.addEventListener('change', function () {
            currentArtist = artistSel.value;
            currentPage   = 1;
            fetchSongs();
        });

        albumSel.addEventListener('change', function () {
            currentAlbum = albumSel.value;
            currentPage  = 1;
            fetchSongs();
        });

        fetchSongs();
    });
}());
