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
- All three plugin admin pages require `edit_theme_options`, so an Editor-role account sees
  the Comics menu but none of Config, Debug or Import.

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

`POST /?ceopaypalipn` discards anything PayPal does not confirm with a literal `VERIFIED`, so
it cannot be exercised without either a PayPal sandbox account or a local stand-in. Three
filters exist to make the local route possible; drop this in `wp-content/mu-plugins/`:

```php
<?php
// Answer the verification handshake ourselves.
add_filter( 'ceo_paypal_ipn_endpoint', function () {
	return 'http://127.0.0.1:8080/ipn-stub.php'; // a file that echoes: VERIFIED
} );

// Capture the notification email instead of sending it.
add_filter( 'pre_wp_mail', function ( $null, $atts ) {
	file_put_contents( WP_CONTENT_DIR . '/ce-mail.log', json_encode( $atts ) . "\n", FILE_APPEND );
	return true;
}, 10, 2 );
```

Set the three-letter PayPal currency on Comic Easel's Buy Comic settings tab. Existing stores
must choose and save it once after upgrading; checkout stays hidden until both the merchant
address and currency are configured. `ceo_paypal_expected_currency` can override that setting
for integrations that hide the built-in tab, and the same resolved value is used by checkout
and IPN validation. `ceo_paypal_max_cart_items` is filterable too.

Then POST to `/?ceopaypalipn` directly. The reject paths matter as much as the accept path,
because each should say *why* in the notification email rather than silently doing nothing:

| Case | Expected |
|---|---|
| stub answers anything but `VERIFIED` | nothing written, no mail |
| valid: right payee, configured currency and amount | comic marked Sold, owner emailed |
| `business` is not the configured address | not sold, "REJECTED: payment was not made to the configured PayPal address." |
| `mc_currency` is not the configured currency | not sold, "REJECTED: payment currency ... is not the configured currency ..." |
| `mc_gross` below the asking price | not sold, "REJECTED: amount paid ... is below the asking price" |
| same `txn_id` and status replayed | ignored, no second mail |
| `Pending` then `Completed`, same `txn_id` | Pending ignored; Completed marks it Sold |

That last row is worth keeping: ignored statuses never enter the replay ledger, so a Pending
notification cannot swallow the Completed one that follows it.

## Always

Run with `WP_DEBUG` and `WP_DEBUG_LOG` on, and treat any notice or warning originating in a
plugin file as a finding.
