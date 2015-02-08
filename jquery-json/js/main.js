jQuery(function($) {
    $.getJSON('http://music.cryns.dev/wp-json/posts/?type=cryns_audio_file')
        .success(function(response) {
            var $content = $('#content')
                .html(response[0].title);

            $content.find('img').each(function() {
                var $this = $(this);
                $this.height($this.height() / 2);
                $this.width($this.width() / 2);
            });

            $('#title')
                .html(response[0].title)
                .attr('href', response[0].link);
        });
});