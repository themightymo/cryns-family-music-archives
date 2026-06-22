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
        var audio = player.querySelector('[data-cfma-audio]');
        var playButton = player.querySelector('[data-cfma-play]');
        var seek = player.querySelector('[data-cfma-seek]');
        var current = player.querySelector('[data-cfma-current]');
        var duration = player.querySelector('[data-cfma-duration]');
        var muteButton = player.querySelector('[data-cfma-mute]');
        var volume = player.querySelector('[data-cfma-volume]');
        var isSeeking = false;

        if (!audio || !playButton || !seek) return;

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

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-cfma-player]').forEach(initPlayer);
    });
}());
