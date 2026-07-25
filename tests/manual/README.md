# Manual test setup

The unit suite in `tests/` covers the plugin's logic without WordPress. It cannot cover the
template and admin-screen code, which has no function boundary to test — the settings forms,
the comic meta boxes, the widget forms, the admin list columns. Those need a real WordPress.

This is the recipe, written down because most of it is non-obvious and every item below is a
trap that makes a working feature look broken.

## Getting a WordPress to test against

No Docker or MySQL required:

1. Unpack WordPress core somewhere disposable.
2. Add the official [`sqlite-database-integration`](https://wordpress.org/plugins/sqlite-database-integration/)
   plugin and copy its `db.copy` to `wp-content/db.php` as its readme describes.
3. Serve with `php -S localhost:8080` from the WordPress root.

SQLite is fine for admin screens, meta boxes, widgets and escaping. It is **not** reliable
for this plugin's raw SQL: the calendar and archive queries use MySQL-only date functions
(`YEAR`, `DATE_ADD`, `DATE_FORMAT`, `DAYOFMONTH`). Use MySQL if those are what you are
testing, or rely on `tests/ArchiveQueryTest.php`, which asserts the emitted SQL directly.

## Installing the plugin

- The directory **must** be named `comic-easel`. `widgets/casthover.php` hardcodes
  `plugins_url('comic-easel/css/casthover.css')`, so a renamed folder loses that widget's
  CSS and JS.
- **Activate through the Plugins screen.** Activation runs
  `ALTER TABLE wp_terms ADD menu_order`, and a `get_terms_orderby` filter rewrites
  `orderby=menu_order` to `t.menu_order`. Without that column, every chapter-ordered query
  errors and silently returns nothing.
- Use a **classic** theme such as Twenty Twenty-One. Block themes break the classic widgets
  screen, and the archive-thumbnail injection is gated on `in_the_loop()`, which core's Query
  Loop block does not set.
- Re-save Settings → Permalinks after activating, and again after changing any slug option.

## Fixture content

- **2 chapters, each with a non-zero Order.** Order defaults to 0, and 0 makes
  `[comic-archive list=4]` render empty and chapter prev/next navigation do nothing.
- 2 characters, 1 location.
- **4 published comics, each with a featured image.** The featured image *is* the comic —
  without one, the comic renders as nothing but an HTML comment.
- One future-dated comic (for the Scheduled Posts widget), one draft and one
  password-protected comic (both should be refused by `[buycomic]`).
- Leave `refer-only` **empty**. Any value in it hides the comic from every visitor whose
  referrer does not match exactly.
- To exercise escaping, seed `transcript`, `comic-hovertext` and `comic-html-above` with
  values containing `<b>`, `&lt;b&gt;`, a double quote and an apostrophe. Seed some of them
  through the **Custom Fields** panel as well as through the plugin's meta boxes: the comic
  post type declares `custom-fields` support and these keys are not protected, so the two
  routes store values in different shapes and a display bug can show up in one but not the
  other.

## Options worth knowing about

Set on the Config page, and verify on the **Debug** page — that screen dumps the whole
config array and is the quickest assertion target for any option write.

- `disable_related_comics` defaults to **on**, so the related-comics hook looks broken until
  you turn it off.
- `enable_transcripts_in_comic_posts` **on** makes the `[transcript]` shortcode return
  nothing. The two are mutually exclusive by design.
- `[buycomic]` needs `?id=<comic_id>` in the URL; a bare `[buycomic]` page renders empty. It
  is gated on `buy_comic_sell_print` / `buy_comic_sell_original`, not on `enable_buy_comic`
  (that option only controls the "Buy!" nav links).
- `enable_chapter_in_url` puts a literal `%chapters%` in the permalink of any comic that has
  no chapter term.
- All four plugin admin pages require `edit_theme_options`, so an Editor-role account sees
  the Comics menu but none of Config, Monetize, Debug or Import.

## Seeing a comic actually render

The plugin displays comics through action hooks a theme has to fire. It never fires them
itself, so on a stock theme **no comic wrapper, navigation or hovertext appears at all** —
you see only the theme's own featured image. That is expected, not a bug.

The minimum test theme is a classic theme whose `single.php` calls
`do_action('comic-area');`, plus `add_theme_support('post-thumbnails')` in `functions.php`
(without it the comic editor has no Featured Image box). Do **not** declare
`post-formats` support: the blog-post renderer branches on it and calls
`get_template_part('content','comic')`, which no stock theme provides, so that hook silently
renders nothing.

Reachable with no theme support at all, via shortcodes on an ordinary page:
`[comic-archive list=2]`, `[cast-page]`, `[showcomic]`, `[comic-archive-dropdown]`, and
`[buycomic]` with `?id=N`.

## The PayPal endpoint

`POST /?ceopaypalipn` talks to PayPal directly, with the hostname written into the code, so
there is no supported way to exercise it against a local stub. Testing it means either
editing that hostname in a scratch copy or using a PayPal sandbox account.

## Always

Run with `WP_DEBUG` and `WP_DEBUG_LOG` on, and treat any notice or warning originating in a
plugin file as a finding.
