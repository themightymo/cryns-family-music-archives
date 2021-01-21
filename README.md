# Cryns Family Music Archives
A plugin that generates mp3 playlists on archive pages and single posts for the Cryns Family Music Archives.

* The plugin works out of the box on every WordPress theme.
* It creates an Audio File custom post type and adds taxonomies for Band Name, Album Title, etc.
* It utilizes the "audio_file" custom field.  You'll want to use ACF or similar to create this field on your Audio File post types.

![Single audio post view using default Twenty Seventeen WordPress theme](https://music-cryns-com.s3.amazonaws.com/wp-content/uploads/2017/08/single.png)

![Archive/Taxonomy view using default Twenty Seventeen WordPress theme](https://music-cryns-com.s3.amazonaws.com/wp-content/uploads/2017/08/archive-playlist.png)

I've included the Advanced Custom Fields (ACF) import file in this repository so you'll be rocking with all the Audio File post types taxonomies + taxonomy images (e.g. album covers).


## Roadmap:
1.  Check issues tab in Github.

## Discussion
1.  Maybe use media library rather than posts...not sure about this, because we would need to use media meta, which is not really in most people's workflows...  I do like the idea of using the media library - i.e. go and get all audio files out of the media library...  This would be an AMAZING solution for folks who don't use the taxonomies.  I actually think maybe all of the taxonomy stuff might *belong* on the media post type rather than the *cryns_audio_file* post type.  Just brainstorming here...  USE CASE #1: Podcaster who does not categorize anything.  USE CASE #2:...
