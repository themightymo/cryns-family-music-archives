(function () {
    'use strict';

    function formatTime(seconds) {
        if (!Number.isFinite(seconds) || seconds < 0) {
            return '00:00';
        }

        var total = Math.floor(seconds);
        var hours = Math.floor(total / 3600);
        var minutes = Math.floor((total % 3600) / 60);
        var secs = total % 60;

        if (hours) {
            return String(hours) + ':' + String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        }

        return String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }

    function paintRange(range, value, max) {
        var percent = max > 0 ? Math.min(100, Math.max(0, (value / max) * 100)) : 0;
        range.style.setProperty('--cfma-range-progress', percent + '%');
    }

    function initPlayer(player) {
        if (player.dataset.cfmaPlayerReady === '1') return;

        var audio = player.querySelector('[data-cfma-audio]');
        var playButton = player.querySelector('[data-cfma-play]');
        var seek = player.querySelector('[data-cfma-seek]');
        var current = player.querySelector('[data-cfma-current]');
        var duration = player.querySelector('[data-cfma-duration]');
        var muteButton = player.querySelector('[data-cfma-mute]');
        var volume = player.querySelector('[data-cfma-volume]');
        var isSeeking = false;

        if (!audio || !playButton || !seek) return;
        player.dataset.cfmaPlayerReady = '1';

        function updatePlayState() {
            var isPlaying = !audio.paused && !audio.ended;
            playButton.classList.toggle('is-playing', isPlaying);
            playButton.setAttribute('aria-label', isPlaying ? 'Pause audio' : 'Play audio');
        }

        function updateDuration() {
            var total = audio.duration || 0;
            seek.max = total || 100;
            duration.textContent = formatTime(total);
            paintRange(seek, audio.currentTime || 0, total || 100);
        }

        function updateProgress() {
            if (isSeeking) return;
            var total = audio.duration || 0;
            seek.value = audio.currentTime || 0;
            current.textContent = formatTime(audio.currentTime || 0);
            paintRange(seek, audio.currentTime || 0, total || 100);
        }

        function updateVolume() {
            var muted = audio.muted || audio.volume === 0;
            player.classList.toggle('is-muted', muted);
            muteButton.setAttribute('aria-label', muted ? 'Unmute audio' : 'Mute audio');
            if (volume) {
                volume.value = muted ? 0 : audio.volume;
                paintRange(volume, volume.value, 1);
            }
        }

        playButton.addEventListener('click', function () {
            if (audio.paused || audio.ended) {
                audio.play().catch(function () {});
            } else {
                audio.pause();
            }
        });

        player.querySelectorAll('[data-cfma-skip]').forEach(function (button) {
            button.addEventListener('click', function () {
                var delta = parseInt(button.getAttribute('data-cfma-skip'), 10) || 0;
                var total = audio.duration || 0;
                audio.currentTime = Math.min(total || Infinity, Math.max(0, audio.currentTime + delta));
                updateProgress();
            });
        });

        seek.addEventListener('input', function () {
            isSeeking = true;
            current.textContent = formatTime(parseFloat(seek.value) || 0);
            paintRange(seek, parseFloat(seek.value) || 0, parseFloat(seek.max) || 100);
        });

        seek.addEventListener('change', function () {
            audio.currentTime = parseFloat(seek.value) || 0;
            isSeeking = false;
            updateProgress();
        });

        if (muteButton) {
            muteButton.addEventListener('click', function () {
                audio.muted = !audio.muted;
                updateVolume();
            });
        }

        if (volume) {
            volume.addEventListener('input', function () {
                audio.volume = parseFloat(volume.value);
                audio.muted = audio.volume === 0;
                updateVolume();
            });
        }

        audio.addEventListener('loadedmetadata', updateDuration);
        audio.addEventListener('durationchange', updateDuration);
        audio.addEventListener('timeupdate', updateProgress);
        audio.addEventListener('play', updatePlayState);
        audio.addEventListener('pause', updatePlayState);
        audio.addEventListener('ended', updatePlayState);
        audio.addEventListener('volumechange', updateVolume);

        updateDuration();
        updateProgress();
        updatePlayState();
        updateVolume();
    }

    function initAll(root) {
        (root || document).querySelectorAll('[data-cfma-player]').forEach(initPlayer);
        (root || document).querySelectorAll('[data-cfma-playlist]').forEach(initPlaylist);
    }

    var playlistRegistry = [];
    var activePlaylistController = null;

    function initPlaylist(playlist) {
        if (playlist.dataset.cfmaPlaylistReady === '1') return;

        var tracks = [];
        try {
            tracks = JSON.parse(playlist.getAttribute('data-cfma-tracks') || '[]');
        } catch (error) {
            tracks = [];
        }

        var audio = playlist.querySelector('[data-cfma-audio]');
        var playButtons = playlist.querySelectorAll('[data-cfma-play], [data-cfma-playlist-play]');
        var prevButtons = playlist.querySelectorAll('[data-cfma-prev]');
        var nextButtons = playlist.querySelectorAll('[data-cfma-next]');
        var seeks = playlist.querySelectorAll('[data-cfma-seek]');
        var currents = playlist.querySelectorAll('[data-cfma-current]');
        var durations = playlist.querySelectorAll('[data-cfma-duration]');
        var volumes = playlist.querySelectorAll('[data-cfma-volume]');
        var muteButtons = playlist.querySelectorAll('[data-cfma-mute]');
        var nowTitle = playlist.querySelector('[data-cfma-now-title]');
        var nowArtist = playlist.querySelector('[data-cfma-now-artist]');
        var nowThumb = playlist.querySelector('.cfma-now-thumb');
        var rows = playlist.querySelectorAll('[data-cfma-track-index]');
        var activeIndex = 0;
        var isSeeking = false;

        if (!audio || !tracks.length || !seeks.length) return;
        playlist.dataset.cfmaPlaylistReady = '1';

        var controller = {
            playlist: playlist,
            pause: function () {
                audio.pause();
            }
        };
        playlistRegistry.push(controller);

        function currentTrack() {
            return tracks[activeIndex] || tracks[0];
        }

        function activateThisPlaylist() {
            if (activePlaylistController === controller) {
                return;
            }

            playlistRegistry.forEach(function (registeredController) {
                if (registeredController !== controller) {
                    registeredController.pause();
                    registeredController.playlist.classList.remove('is-active-playlist');
                }
            });

            activePlaylistController = controller;
            playlist.classList.add('is-active-playlist');
        }

        function setButtonText(isPlaying) {
            playButtons.forEach(function (button) {
                var label = isPlaying ? 'Pause' : 'Play';
                if (button.hasAttribute('data-cfma-playlist-play')) {
                    button.innerHTML = '<span aria-hidden="true">' + (isPlaying ? '||' : '&#9658;') + '</span><span>' + label + '</span>';
                } else if (button.classList.contains('cfma-playlist-play-button')) {
                    button.innerHTML = '<span aria-hidden="true">' + (isPlaying ? '||' : '&#9658;') + '</span>';
                } else {
                    button.textContent = label;
                }
                button.setAttribute('aria-label', isPlaying ? 'Pause playlist' : 'Play playlist');
            });
        }

        function updateRows() {
            rows.forEach(function (row) {
                var isActive = parseInt(row.getAttribute('data-cfma-track-index'), 10) === activeIndex;
                row.classList.toggle('is-active', isActive);
                row.setAttribute('aria-current', isActive ? 'true' : 'false');
            });
        }

        function updateNowPlaying() {
            var track = currentTrack();
            if (nowTitle) {
                nowTitle.textContent = track.title || '';
            }
            if (nowArtist) {
                nowArtist.textContent = track.artist && track.artist.length ? track.artist.join(', ') : '';
            }
            durations.forEach(function (duration) {
                duration.textContent = track.duration || '00:00';
            });
            if (nowThumb) {
                nowThumb.innerHTML = track.art ? '<img src="' + track.art + '" alt="">' : '<span>&#9834;</span>';
            }
        }

        function loadTrack(index, shouldPlay) {
            activeIndex = (index + tracks.length) % tracks.length;
            var track = currentTrack();
            audio.src = track.url;
            audio.type = track.mime || 'audio/mpeg';
            audio.load();
            seeks.forEach(function (seek) {
                seek.value = 0;
                paintRange(seek, 0, 100);
            });
            currents.forEach(function (current) {
                current.textContent = '00:00';
            });
            updateNowPlaying();
            updateRows();
            if (shouldPlay) {
                activateThisPlaylist();
                audio.play().catch(function () {});
            }
        }

        function togglePlay() {
            if (!audio.src) {
                loadTrack(activeIndex, true);
                return;
            }
            if (audio.paused || audio.ended) {
                activateThisPlaylist();
                audio.play().catch(function () {});
            } else {
                audio.pause();
            }
        }

        function updateDuration() {
            var total = audio.duration || 0;
            seeks.forEach(function (seek) {
                seek.max = total || 100;
                paintRange(seek, audio.currentTime || 0, total || 100);
            });
            durations.forEach(function (duration) {
                duration.textContent = total ? formatTime(total) : (currentTrack().duration || '00:00');
            });
        }

        function updateProgress() {
            if (isSeeking) return;
            var total = audio.duration || 0;
            seeks.forEach(function (seek) {
                seek.value = audio.currentTime || 0;
                paintRange(seek, audio.currentTime || 0, total || 100);
            });
            currents.forEach(function (current) {
                current.textContent = formatTime(audio.currentTime || 0);
            });
        }

        function updatePlayState() {
            playlist.classList.toggle('is-playing', !audio.paused && !audio.ended);
            setButtonText(!audio.paused && !audio.ended);
        }

        function updateVolume() {
            var muted = audio.muted || audio.volume === 0;
            playlist.classList.toggle('is-muted', muted);
            volumes.forEach(function (volume) {
                volume.value = muted ? 0 : audio.volume;
                paintRange(volume, volume.value, 1);
            });
            muteButtons.forEach(function (muteButton) {
                muteButton.textContent = muted ? 'Unmute' : 'Mute';
                muteButton.setAttribute('aria-label', muted ? 'Unmute playlist' : 'Mute playlist');
            });
        }

        playButtons.forEach(function (button) {
            button.addEventListener('click', togglePlay);
        });

        rows.forEach(function (row) {
            row.addEventListener('click', function () {
                loadTrack(parseInt(row.getAttribute('data-cfma-track-index'), 10) || 0, true);
            });
        });

        prevButtons.forEach(function (prevButton) {
            prevButton.addEventListener('click', function () {
                loadTrack(activeIndex - 1, true);
            });
        });

        nextButtons.forEach(function (nextButton) {
            nextButton.addEventListener('click', function () {
                loadTrack(activeIndex + 1, true);
            });
        });

        seeks.forEach(function (seek) {
            seek.addEventListener('input', function () {
                var value = parseFloat(seek.value) || 0;
                var max = parseFloat(seek.max) || 100;
                isSeeking = true;
                currents.forEach(function (current) {
                    current.textContent = formatTime(value);
                });
                seeks.forEach(function (mirroredSeek) {
                    mirroredSeek.value = value;
                    mirroredSeek.max = max;
                    paintRange(mirroredSeek, value, max);
                });
            });

            seek.addEventListener('change', function () {
                audio.currentTime = parseFloat(seek.value) || 0;
                isSeeking = false;
                updateProgress();
            });
        });

        if (volumes.length) {
            audio.volume = parseFloat(volumes[0].value) || 0.8;
        }

        volumes.forEach(function (volume) {
            volume.addEventListener('input', function () {
                audio.volume = parseFloat(volume.value);
                audio.muted = audio.volume === 0;
                updateVolume();
            });
        });

        muteButtons.forEach(function (muteButton) {
            muteButton.addEventListener('click', function () {
                audio.muted = !audio.muted;
                updateVolume();
            });
        });

        audio.addEventListener('loadedmetadata', updateDuration);
        audio.addEventListener('durationchange', updateDuration);
        audio.addEventListener('timeupdate', updateProgress);
        audio.addEventListener('play', updatePlayState);
        audio.addEventListener('pause', updatePlayState);
        audio.addEventListener('ended', function () {
            if (activeIndex < tracks.length - 1) {
                loadTrack(activeIndex + 1, true);
            } else {
                updatePlayState();
            }
        });
        audio.addEventListener('volumechange', updateVolume);

        if (!activePlaylistController) {
            activateThisPlaylist();
        }

        loadTrack(0, false);
        updatePlayState();
        updateVolume();
    }

    window.cfmaAudioPlayer = {
        initAll: initAll
    };

    document.addEventListener('DOMContentLoaded', function () {
        initAll(document);
    });
}());
