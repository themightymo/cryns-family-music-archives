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

## Site Infrastructure

### Local Development Environment
- **Tool:** Local by Flywheel (Local.app)
- **MySQL socket:** `/Users/mighty1/Library/Application Support/Local/run/DzXqBLu20/mysql/mysqld.sock`
- **MySQL binary:** `/Users/mighty1/Library/Application Support/Local/lightning-services/mysql-8.0.16+6/bin/darwin/bin/mysql`
- **DB name / user / pass:** `local` / `root` / `root`
- **WP-CLI status:** broken (Fatal error in DocParser.php — do not use)
- Use the MySQL binary + socket directly for any DB queries.

Example DB query pattern:
```bash
MYSQL="/Users/mighty1/Library/Application Support/Local/lightning-services/mysql-8.0.16+6/bin/darwin/bin/mysql"
SOCK="/Users/mighty1/Library/Application Support/Local/run/DzXqBLu20/mysql/mysqld.sock"
"$MYSQL" -u root -proot -S "$SOCK" local -e "SELECT ..."
```

---

## Homepage

- **Page ID:** 1697 (set as `page_on_front` in `wp_options`)
- **Page builder:** Beaver Builder (`_fl_builder_data` postmeta, PHP-serialized)
- **Theme:** BB Theme (`bb-theme`) — no `front-page.php` template; page content drives the layout
- **BB cache files** for page 1697 are in `wp-content/uploads/bb-plugin/cache/1697-layout*`

### Homepage layout (two rows)

**Row 1** — full-width, one column:
- `rich-text` module (node `6009117d589be`): houses the song filter UI — currently `[cfma_song_filter]` shortcode
- `sidebar` module (node `600911c6c48dc`): renders widget area `blog-sidebar`

**Row 2** — two columns:
- `heading` module (node `357uswlct8ea`): "Recently Added:"
- `post-grid` module (node `xlzjema2rkw4`): 3 most-recent standard *posts* (not audio files), FacetWP disabled
- `widget` module (node `j5xchf8eu3zm`): Ajax Search Lite widget (`AJAXY_SF_WIDGET`)

---

## FacetWP (being removed)

Three plugins installed: `facetwp`, `facetwp-beaver-builder`, `facetwp-relevanssi`.
FacetWP settings are stored in `wp_options` as `facetwp_settings` (JSON).
The two configured facets were:
- `artist` — dropdown, source `tax/cryns_artist`, ordered by count
- `album_title` — dropdown, source `tax/cryns_album_title`, ordered by count

These have been replaced by the `[cfma_song_filter]` shortcode (see below).

---

## `[cfma_song_filter]` Shortcode

Registered in the main plugin file. Replaces all FacetWP homepage functionality.

**What it renders:**
- Artist dropdown (populated server-side via `get_terms('cryns_artist')`)
- Album Title dropdown (populated server-side via `get_terms('cryns_album_title')`)
- Active-filter chips with × dismiss buttons
- "Total Results: N" count
- Song list: title link + inline `<audio>` player, fetched via REST API
- Numbered pagination

**How it works:**
- PHP shortcode renders the dropdowns and skeleton HTML
- `js/song-filter.js` (vanilla JS, no jQuery) drives all filtering/pagination via `fetch()` against the standard WP REST endpoint:
  `GET /wp-json/wp/v2/cryns_audio_file?_fields=id,title,link,audio_file&per_page=20&orderby=date&order=desc`
- Taxonomy filter params: `cryns_artist={term_id}` and `cryns_album_title={term_id}`
- Response headers `X-WP-Total` and `X-WP-TotalPages` drive count + pagination
- Styles are in `media-player-style.css`

---

## Installed Plugins of Note

| Plugin dir | Purpose |
|---|---|
| `facetwp` | Filterable archive UI — **being removed** |
| `facetwp-beaver-builder` | FacetWP modules for BB — **being removed** |
| `facetwp-relevanssi` | FacetWP + Relevanssi integration — **being removed** |
| `bb-plugin` | Beaver Builder page builder |
| `bb-theme-builder` | Beaver Builder Themer (archive/singular layouts) |
| `advanced-custom-fields-pro` | ACF Pro — required |
| `relevanssi` | Search enhancement |
| `ajax-search-lite` | Ajax Search Lite widget (`AJAXY_SF_WIDGET`) — on homepage row 2 |

---

## Reading Beaver Builder Layout Data

BB stores layout as a PHP-serialized object graph in `_fl_builder_data` (published) and `_fl_builder_draft` (draft/editing) postmeta. To inspect it:

```bash
"$MYSQL" ... -e "SELECT meta_value FROM wp_postmeta WHERE post_id=1697 AND meta_key='_fl_builder_data';" > /tmp/bb.txt
php -r "
\$raw = file_get_contents('/tmp/bb.txt');
\$lines = explode(PHP_EOL, \$raw, 2);
\$data = @unserialize(\$lines[1]);
foreach (\$data as \$id => \$node) {
    if (\$node->type === 'module') echo \$id . ' ' . \$node->settings->type . PHP_EOL;
}"
```

To update a module's text field, write a PHP CLI script that bootstraps WordPress (`require wp-load.php`), calls `get_post_meta`, unserializes, modifies, re-serializes, and calls `update_post_meta`. Then delete the BB cache files for the page.

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
