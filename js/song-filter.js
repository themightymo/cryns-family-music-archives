(function () {
    'use strict';

    if (typeof cfmaFilter === 'undefined') return;

    const { feedUrl, perPage } = cfmaFilter;
    let currentPage       = 1;
    let currentArtist     = '';
    let currentAlbum      = '';
    let currentMusicians  = '';
    let currentWrittenBy  = '';
    let totalPages        = 1;
    let totalResults      = 0;
    let isFetching        = false;

    // --- URL state ---

    function pushState() {
        const params = new URLSearchParams();
        if (currentArtist)    params.set('cfma_artist',     currentArtist);
        if (currentAlbum)     params.set('cfma_album',      currentAlbum);
        if (currentMusicians) params.set('cfma_musicians',  currentMusicians);
        if (currentWrittenBy) params.set('cfma_written_by', currentWrittenBy);
        if (currentPage > 1)  params.set('cfma_page',       currentPage);
        const qs = params.toString();
        history.replaceState(null, '', qs ? '?' + qs : window.location.pathname);
    }

    function readState() {
        const params = new URLSearchParams(window.location.search);
        currentArtist    = params.get('cfma_artist')     || '';
        currentAlbum     = params.get('cfma_album')      || '';
        currentMusicians = params.get('cfma_musicians')  || '';
        currentWrittenBy = params.get('cfma_written_by') || '';
        currentPage      = parseInt(params.get('cfma_page') || '1', 10);

        var artistIds = currentArtist ? currentArtist.split(',') : [];
        document.querySelectorAll('.cfma-artist-cb').forEach(function (cb) {
            cb.checked = artistIds.indexOf(cb.value) !== -1;
        });

        var albumIds = currentAlbum ? currentAlbum.split(',') : [];
        document.querySelectorAll('.cfma-album-cb').forEach(function (cb) {
            cb.checked = albumIds.indexOf(cb.value) !== -1;
        });

        var musicianIds = currentMusicians ? currentMusicians.split(',') : [];
        document.querySelectorAll('.cfma-musicians-cb').forEach(function (cb) {
            cb.checked = musicianIds.indexOf(cb.value) !== -1;
        });

        var writtenByIds = currentWrittenBy ? currentWrittenBy.split(',') : [];
        document.querySelectorAll('.cfma-written-by-cb').forEach(function (cb) {
            cb.checked = writtenByIds.indexOf(cb.value) !== -1;
        });
    }

    // --- REST API ---

    function buildUrl(page) {
        const params = new URLSearchParams({
            per_page: perPage,
            page:     page,
        });
        if (currentArtist)    params.set('cryns_artist',      currentArtist);
        if (currentAlbum)     params.set('cryns_album_title', currentAlbum);
        if (currentMusicians) params.set('cryns_musicians',   currentMusicians);
        if (currentWrittenBy) params.set('cryns_written_by',  currentWrittenBy);
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

    function renderAudioPlayer(audio) {
        const mime = escHtml(audio.mime || 'audio/mpeg');
        const src = escHtml(audio.url);

        return '<div class="cfma-single-player cfma-feed-player" data-cfma-player>' +
            '<div class="cfma-single-player-shell">' +
            '<div class="cfma-single-player-actions">' +
            '<button type="button" class="cfma-player-btn cfma-player-skip" data-cfma-skip="-15" aria-label="Skip back 15 seconds">' +
            '<svg aria-hidden="true" fill="none" height="32" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="32" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 2v6h6M2.66 15.57a10 10 0 1 0 .57-8.38"></path><text fill="currentColor" font-size="7" font-weight="700" stroke="none" text-anchor="middle" x="12" y="17">15</text></svg>' +
            '</button>' +
            '<button type="button" class="cfma-player-btn cfma-player-play" data-cfma-play aria-label="Play audio">' +
            '<svg class="cfma-play-svg" aria-hidden="true" fill="currentColor" height="32" viewBox="0 0 24 24" width="32" xmlns="http://www.w3.org/2000/svg"><path d="M8 5v14l11-7z"></path></svg>' +
            '<svg class="cfma-pause-svg" aria-hidden="true" fill="currentColor" height="32" viewBox="0 0 24 24" width="32" xmlns="http://www.w3.org/2000/svg"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"></path></svg>' +
            '</button>' +
            '<button type="button" class="cfma-player-btn cfma-player-skip" data-cfma-skip="15" aria-label="Skip forward 15 seconds">' +
            '<svg aria-hidden="true" fill="none" height="32" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="32" xmlns="http://www.w3.org/2000/svg"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38"></path><text fill="currentColor" font-size="7" font-weight="700" stroke="none" text-anchor="middle" x="12" y="17">15</text></svg>' +
            '</button>' +
            '</div>' +
            '<div class="cfma-single-player-divider" aria-hidden="true"></div>' +
            '<div class="cfma-single-player-progress">' +
            '<span class="cfma-player-time" data-cfma-current>00:00</span>' +
            '<input class="cfma-player-range cfma-player-seek" data-cfma-seek type="range" min="0" max="100" value="0" step="0.1" aria-label="Audio progress">' +
            '<span class="cfma-player-time" data-cfma-duration>00:00</span>' +
            '<button type="button" class="cfma-player-btn cfma-player-volume" data-cfma-mute aria-label="Mute audio">' +
            '<svg aria-hidden="true" fill="currentColor" height="28" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"></path></svg>' +
            '</button>' +
            '<a class="cfma-player-menu" href="' + src + '" download aria-label="Download audio">' +
            '<svg aria-hidden="true" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>' +
            '</a>' +
            '</div>' +
            '</div>' +
            '<audio preload="metadata" data-cfma-audio><source type="' + mime + '" src="' + src + '"></audio>' +
            '</div>';
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
                playerHtml = renderAudioPlayer(audio);
            }
            const playlistNote = post.has_playlist
                ? '<p class="cfma-playlist-note">&#127911; This post has a full audio playlist &mdash; <a href="' + link + '">click to listen to all tracks</a>.</p>'
                : '';
            return '<li class="cfma-song-item">' +
                '<h2 class="cfma-song-title">' + badge + ' <a href="' + link + '">' + title + '</a></h2>' +
                playerHtml +
                playlistNote +
                '</li>';
        });

        container.innerHTML = '<ul class="cfma-song-list">' + items.join('') + '</ul>';
        if (window.cfmaAudioPlayer && typeof window.cfmaAudioPlayer.initAll === 'function') {
            window.cfmaAudioPlayer.initAll(container);
        }
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

    function countIds(value) {
        return value ? value.split(',').filter(Boolean).length : 0;
    }

    function getActiveFilterCount() {
        return countIds(currentArtist) +
            countIds(currentAlbum) +
            countIds(currentMusicians) +
            countIds(currentWrittenBy);
    }

    function updateMobileFilterButton() {
        var countEl = document.getElementById('cfma-mobile-filter-count');
        if (!countEl) return;

        var count = getActiveFilterCount();
        if (count) {
            countEl.hidden = false;
            countEl.textContent = count + ' selected';
        } else {
            countEl.hidden = true;
            countEl.textContent = '';
        }
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
        if (currentMusicians) {
            const labels = getCheckedLabels('.cfma-musicians-cb').map(escHtml).join(', ');
            chips.push(
                '<span class="cfma-chip">Musicians: ' + (labels || 'selected') +
                ' <button class="cfma-chip-clear" data-clear="musicians" aria-label="Remove musicians filter">&times;</button></span>'
            );
        }
        if (currentWrittenBy) {
            const labels = getCheckedLabels('.cfma-written-by-cb').map(escHtml).join(', ');
            chips.push(
                '<span class="cfma-chip">Written by: ' + (labels || 'selected') +
                ' <button class="cfma-chip-clear" data-clear="written_by" aria-label="Remove written by filter">&times;</button></span>'
            );
        }

        container.innerHTML = chips.join('');
        updateMobileFilterButton();

        container.querySelectorAll('.cfma-chip-clear').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const which = btn.dataset.clear;
                if (which === 'artist') {
                    currentArtist = '';
                    document.querySelectorAll('.cfma-artist-cb').forEach(function (cb) { cb.checked = false; });
                } else if (which === 'album') {
                    currentAlbum = '';
                    document.querySelectorAll('.cfma-album-cb').forEach(function (cb) { cb.checked = false; });
                } else if (which === 'musicians') {
                    currentMusicians = '';
                    document.querySelectorAll('.cfma-musicians-cb').forEach(function (cb) { cb.checked = false; });
                } else if (which === 'written_by') {
                    currentWrittenBy = '';
                    document.querySelectorAll('.cfma-written-by-cb').forEach(function (cb) { cb.checked = false; });
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
        updateMobileFilterButton();

        var sidebar = document.querySelector('.cfma-sidebar');
        var mobileFilterToggle = document.querySelector('.cfma-mobile-filter-toggle');
        if (sidebar && mobileFilterToggle) {
            mobileFilterToggle.addEventListener('click', function () {
                var isOpen = sidebar.classList.toggle('is-open');
                mobileFilterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        initCheckboxSearch('cfma-artist-search',     'cfma-artist-checkboxes');
        initCheckboxSearch('cfma-album-search',      'cfma-album-checkboxes');
        initCheckboxSearch('cfma-musicians-search',  'cfma-musicians-checkboxes');
        initCheckboxSearch('cfma-written-by-search', 'cfma-written-by-checkboxes');

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

        document.querySelectorAll('.cfma-musicians-cb').forEach(function (cb) {
            cb.addEventListener('change', function () {
                currentMusicians = getCheckedValues('.cfma-musicians-cb');
                currentPage      = 1;
                fetchSongs();
            });
        });

        document.querySelectorAll('.cfma-written-by-cb').forEach(function (cb) {
            cb.addEventListener('change', function () {
                currentWrittenBy = getCheckedValues('.cfma-written-by-cb');
                currentPage      = 1;
                fetchSongs();
            });
        });

        fetchSongs();
    });
}());
