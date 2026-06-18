# Cryns Family Music Archives — Agent Reference

A WordPress plugin that is the entire data layer and playback engine for a personal family music archive site (808+ songs). It registers a custom post type, eight taxonomies, ACF field groups, REST API endpoints, and front-end rendering hooks.

---

## Custom Post Type: `cryns_audio_file`

Each audio recording is a `cryns_audio_file` post.

| Property | Value |
|---|---|
| URL slug | `songs` → `/songs/{post-slug}/` |
| Archive URL | `/songs/` |
| REST API base | `/wp-json/wp/v2/cryns_audio_file` |
| Admin menu position | 3 (below Posts) |
| Supported features | title, editor, custom-fields, author, excerpt, comments, post-formats |
| Post format required for playback | **audio** — the player is only injected when the post format is "audio" |

---

## ACF Custom Fields

Fields are defined in `acf-export-2021-01-21.json` and must be imported into ACF.

### On `cryns_audio_file` posts (field group: "Audio Files")

| ACF field name | ACF type | Returns | Purpose |
|---|---|---|---|
| `audio_file` | file | **attachment ID** (integer) | The uploaded MP3/WAV file |
| `track_number` | number | integer (min 1) | Track position on an album |

**Legacy fallback:** Some older posts store the audio attachment ID under the meta key `Audio File` (with a capital A and space) — the original Custom Field Template format. Every function that reads `audio_file` also checks `Audio File` as a fallback.

### On `cryns_artist` taxonomy terms (field group: "Artist Image")

| ACF field name | ACF type | Returns | Purpose |
|---|---|---|---|
| `artist_image` | image | array (`url`, `width`, `height`, …) | Photo displayed on artist archive pages |

---

## Taxonomies

All are attached to `cryns_audio_file`, registered with `show_in_rest: true`, and exposed in the standard REST API response as arrays of term IDs.

| Taxonomy slug | URL prefix | Hierarchical | Admin column |
|---|---|---|---|
| `cryns_artist` | `/artist/` | yes | no |
| `cryns_album_title` | `/album-title/` | yes | **yes** |
| `cryns_written_by` | `/written-by/` | yes | no |
| `cryns_producer` | `/producer/` | yes | no |
| `cryns_engineer` | `/engineer/` | yes | no |
| `cryns_genre` | `/genre/` | yes | no |
| `cryns_musicians` | `/musicians/` | yes | no |
| `cryns_release_year` | `/release-year/` | **no** (flat/tag-like) | no |

---

## REST API

### Standard endpoint

```
GET /wp-json/wp/v2/cryns_audio_file
```

Supports all standard WP REST query args (`per_page`, `page`, `search`, `orderby`, `order`, `cryns_artist`, `cryns_album_title`, etc.).

The `audio_file` property is added via `register_rest_field` and returns:

```json
"audio_file": {
  "id": 4333,
  "url": "https://example.com/wp-content/uploads/2022/01/song.mp3",
  "title": "The Cage – Jan 2022",
  "mime": "audio/mpeg",
  "filename": "The-Cage-Jan-2022-Toby-Nathaniel.wav"
}
```

Returns `null` when no file is attached.

All ACF fields are also exposed in REST via the `acf/rest_api/field_settings/show_in_rest` filter (requires ACF Pro).

### Custom search endpoint

```
GET /wp-json/custom/v1/search-audio/?s={query}
```

- No authentication required.
- Searches post titles across `cryns_audio_file` AND `attachment` post types.
- Also searches the raw value of the ACF `audio_file` field (catches filename-based matches).
- Returns an array of objects:

```json
[
  {
    "id": 4332,
    "type": "cryns_audio_file",
    "title": "The Cage – Toby and Nathaniel – Jan. 2022",
    "link": "http://musiccryns.local/songs/the-cage-toby-and-nathaniel-jan-2022/"
  }
]
```

### Legacy endpoint (WP REST v1, likely unused)

The `json_prepare_post` filter adds `myextradata.mp3URL` to old-style API responses. This predates the `register_rest_field` approach and can be ignored for new development.

---

## Front-End Rendering

The plugin hooks into `the_content` filter (priority 20) via `add_mp3_to_single_audio_posts()`.

### Single post view (`is_singular('cryns_audio_file')` + post format "audio")

Appends **audio player** + **metadata block** after the post content.

**Player HTML** (from `return_audio_player()`):
```html
<audio class="wp-audio-shortcode" preload="none" style="width:100%;" controls>
  <source type="audio/mpeg" src="{attachment_url}">
</audio>
```

**Metadata HTML** (from `return_audio_meta()`):
```html
<div class="audio-meta">
  <a href="{mp3_url}" target="_blank">Download MP3 File</a>
  | Artist(s): <a href="/artist/toby/">Toby</a>
  | Written By: …
  | Track Number: 5
  , Release Year: <a href="/release-year/2022/">2022</a>
  | Musicians: …
  | Engineer(s): …
  | Producer(s): …
  | Genre(s): …
  | Album Title …
</div>
```

### Archive/taxonomy page (post format "audio" only)

Appends player only — no metadata block.

### `[cryns_audio_playlist]` shortcode

Renders a WordPress native `[playlist]` shortcode populated with the audio attachment IDs from all posts in the current WP_Query. On `cryns_artist` taxonomy pages it also outputs the artist's ACF `artist_image` below the playlist.

The underlying PHP function `cryns_audio_playlist()` can be called directly in theme templates on any archive page.

### Footer

Two actions on `wp_footer`:
- `display_audio_post_count()` — outputs `<div class="cfma-footer-meta">Total Songs Posted: {N}</div>`
- `footer_credits()` — outputs plugin credit link

### CSS

`media-player-style.css` in the plugin root is enqueued on every front-end page.

---

## Key Naming Conventions

| Context | Name |
|---|---|
| Post type | `cryns_audio_file` |
| All taxonomy prefixes | `cryns_` |
| ACF audio file field | `audio_file` (lowercase with underscore) |
| Legacy audio file meta key | `Audio File` (capitalized with space) |
| ACF track number field | `track_number` |
| ACF artist image field | `artist_image` (stored on taxonomy term, not post) |

---

## Dependencies

| Dependency | Required? | Purpose |
|---|---|---|
| Advanced Custom Fields (ACF) | **Required** | `audio_file`, `track_number`, `artist_image` fields |
| ACF Pro | Required for REST exposure | `acf/rest_api` filter |
| Jetpack | Optional | Related posts (headline customized to "Related songs:") |
| Subscribe2 | Optional | `cryns_audio_file` posts included in email subscriptions |

---

## How to Add a New Song

1. Create a new `cryns_audio_file` post.
2. Set the post format to **Audio** (in the Format meta box).
3. Upload the MP3/WAV via the ACF **Audio File** field (stores attachment ID in `audio_file` meta).
4. Optionally fill in **Track Number**.
5. Assign taxonomies: Artist, Album Title, Written By, Release Year, Musicians, Engineer, Producer, Genre.
6. Publish. The player and metadata block render automatically on the single post view.

---

## Common Query Patterns

**All songs by a specific artist (PHP):**
```php
new WP_Query([
  'post_type' => 'cryns_audio_file',
  'tax_query' => [[
    'taxonomy' => 'cryns_artist',
    'field'    => 'slug',
    'terms'    => 'toby-cryns',
  ]],
]);
```

**All songs on an album (REST API):**
```
GET /wp-json/wp/v2/cryns_audio_file?cryns_album_title={term_id}&orderby=meta_value_num&meta_key=track_number&order=asc
```

**Get the playable URL for a post (PHP):**
```php
$file_id  = get_field('audio_file', $post_id);                // ACF (new)
$file_id  = $file_id ?: get_post_meta($post_id, 'Audio File', true); // legacy fallback
$audio_url = wp_get_attachment_url($file_id);
```
