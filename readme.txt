=== Comic Easel ===
Author: Frumph
Contributors: Frumph
Tags: comiceasel, easel, webcomic, comic, webcomic
Requires at least: 4.5
Tested up to: 4.6.1
Stable tag: 1.12
Donate link: http://frumph.net
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Comic Easel allows you to post webcomics to your theme.


== Description ==

Comic Easel Website: [Comic Easel](http://comiceasel.com/ "Comic Easel - Plugin your WebComic")

Comic Easel allows you to incorporate a WebComic using the WordPress Media Library functionality with Navigation into almost any WordPress theme. With just a few modifications of adding *injection* action locations into a theme, you can have the theme of your choice display a comic.

The core reason to use Comic Easel above other WordPress theme's is that you are not limited to the basic ComicPress & Other themes that are specifically designed for WebComics that utilize structures that you do not require or want to make use of. There are a plentiful amount of themes in the WordPress repository that you can now take advantage of that give you tons of options you otherwise wouldn't have had.

With Comic Easel's extra taxonomies to control Character and Locations, you can provide your end readers with a plethora of information that wouldn't have had before that is auto-generated. The Cast Page itself shows how many times a character was in a comic as well as the first comic they were seen in.

Comic Easel is *not* an upgrade to ComicPress, it is a different CMS that has a migration path available from ComicPress to Comic Easel.   The ComicPress theme has functionality that the theme you choose might not.

To Convert your existing ComicPress theme comics to Comic Easel's post type there is a plugin available called CP2CE.

= Features =

- Custom Post Type control of posts.
- Media Library handling of comics.
- As many chapters/stories as you would like.
- Individual navigation per chapter or all.
- Character and Location settings per Comic
- As many comic posts you can do in a day as you want.
- Hovertext on the comic
- Using translate plugins, every comic and post can be multilanguage
- Navigation widget that mimics ComicPress's navigation widget including custom graphic sets that can be pulled from themes
- chapter navigation in a variety of different methods
- can create a gallery of comics for a post
- transcripts
- And more!

= Widgets =

- Chapter Dropdown, brings you to the first comic in the chapter (story)
- Calendar display, show's you what days comic posts were made on, can add images and links to backgrounds.
- Recent Comics, a list of comics that have been posted as of late.
- Thumbnail, display a thumbnail of a random comic, or first/latest comic in a chapter (or all)

= Redirects = 

- `/?latest`  or `/?latest=<chapter-id>` in the url will automatically take the end user to the latest comic, or latest of a specific chapter
- `/?random` in the url will redirect to a random comic out of all the comics.

= Short Codes =

Shortcodes are simple embed statements that you can put into pages/post that display information.

- `[comic-archive list=0/1/2 chapter=# thumbnail=0/1]` Display a list of your comics by individual chapters or all.
* list=0 (default) - All chapters, not in parent->child relationship
* chapter=# if list=0 and chapter=# (# = chapter ID number) do a singular view
* list=1 if list=1 do it for series that has parent->child book->chapter (chapter= will not work)
* list=2 by year archive, will print a list of years the comic has been made in and show all comics for that year
* thumbnail=1 display the thumbnail of the first post it finds 
- `[cast-page]` Display a list of all of your characters, how many comics they were in and when they first appeared
- `[transcript]` Display the transcript of the comic whereever you like within the post
* display=(raw/br/styled*) styled = default [transcript display=raw] = no special output.
- `[randcomic]` Display a random comic inside a blog post
* character=slugname  name=postslug chapter=specific-chapter size=(thumbnail/large/full)

= Action Injection Locations =

A number of injection snippets that you add to your theme, mini navigation for the menubar, comic area and comic blost post area, including post-information is available to customize your theme out with auto generated information.


== Installation == 

= Setting up Thumbnail sizes before adding your comics =

In the WP-ADMIN -> Settings -> Media, you can set the thumbnail widths that you would want to use on your site.

The "thumbnail size" default 150x150 cropped works just fine.  Some users of Comic Easel have noted that it doesn't look the greatest for all comics so they suggest unchecking the box for crop thumbnil and setting the width to 198 (barely less then the width of the sidebars) and then removing the contents of height on thumbnail medium and large sizes.  This is all depends on your comic.

Further down the Media page is the [x] Organize my uploads into month- and year-based folders, this is a *must* have since it will organize your comics into sep. directories for you.

If you don't like the size of your thumbnails you have set, there are several plugins available on the WordPress repository available to regenerate all of your thumbnails.


= Modifying themes to use =

* Modify your theme adding `<?php do_action('comic-area'); ?>` in a position where to display the comic, generally it should be right above the #content and under the menu bar.

Generally the two files to edit is the index.php and the single.php, however some layouts are auto-generated with code and those you will need to seek advice out from their designers, the makers of those particular themes.

There are other "action" area's that you can put into your theme, not just the comic-area.  


`do_action('comic_area');` - This is for the area you want your comic displayed on the home page and single pages.

`do_action('comic_blog_area');` - This is for the blog portion of the comic for the home page only.

`do_action('comic-mini-navigation');` - For menubar's to have mini navigation (prev/next) in them.

`do_action('comic-post-info');` - For inside of the single/archive/search post pages posts, showing more comic info.

`do_action('comic-post-extras');` - Inside the individual post loop, preferably at the bottom after the post div.  Show's a list of related comics.

`do_action('comic-transcript');` - generally used under the_content() to display the transcript of the post, if you do not want to use the [transcript] shortcode, this will make it so that it always displays if there is a transcript

= Adding the Comic area sidebars =

Sidebars for Comic Easel are added automatically since 05/28/2012 They should appear above all of your other sidebars in the widget panel; and can be toggled on and off in the config.


== Frequently Asked Questions ==

Comic Easel Website, Troubleshoot Page: [Comic Easel](http://comiceasel.com/faqs/troubleshoot/ "Comic Easel - Plugin your Website - Troubleshooting Comic Easel")

= The permalinks are not working to go to the comic =

Go to your settings -> permalinks and just click save, the wp_rewrite will refresh.  You need to go to the settings -> permalinks if you ever upgrade enable or disable the Comic Easel plugin.

= Where is Comic Easel's navigation widget? =

The comic navigation widget is only seen if you have the comic sidebar's enabled; even then it only works in the comic sidebars themself, nowhere else.


== Changelog ==
= 1.12 =
* Changed the new option to be off by default and only run on is_single pages if then

= 1.11 =
* Add option to 'remove' featured images from comic posts on non-ComicPress themes automagically, if theme uses the function the_post_thumbnail()

= 1.10 =
* Compatibility check with 4.6

= 1.9.12 =
* Compatibility check with 4.5.3
* Updated english language files

= 1.9.11 =
* Removed loading of webcomics.com information from remotely
* updated to correspond to WordPress 4.5 release

= 1.9.10 =
* Added Content Warning toggle which blur's individual comics in the comic editor (user can click to unblur) courtesy Ryan G.
* Added URL referrer ability to comics that makes it so that you can choose for comics only to be visible if they clicked a link from some place else
* @egypturnash - added option to render chapter dropdown as list of links
* @eypturnash - added highlighting of current chapter to list

= 1.9.9 =
* @Egypturnash - fix ceo_get_adjacent_chapter to work with W3 Total Cache's object cache

= 1.9.8 =
* Put navigation bar back where people expect it to be, will make it an option for 1.9.9

= 1.9.7 =
* Removed all references, css, code and actions to 'motion artist' - no longer supporting
* location/character page override code or custom landing pages, requires ComicPress 4.3+ or templates added from 4.3 to your current
* added !important on .comic-table for the display:table to not have zappbar's overwrite it for proper display
* show entire list on the options page for which chapter to select for home page

= 1.9.6 =
* Introducing Landing Pages for chapters that works with the ComicPress theme
* CSS mods and inclusions for the new ComicPress theme
* removed outdated -moz -webkit -khtml references in the CSS
* options added for landing pages and changing the default nav bar's chapter navigation to go to it or not
* removed bad reference assignment & in the buy-print shortcode
* revamped the query arguments in the thumbnail widget

= 1.9.5 =
* Fix: Make sure the extra CSS for Jetpack's mobile theme, only executes with the Jetpack mobile theme
* Moved most actions and filters to run at 'init' time so they are found at a better time after other plugins are loaded
* Fix: Translation strings for "No comic found." in a couple are done correctly 
* Added some CSS to disable the prev/next for the Jetpack Mobile theme on single-comic pages

= 1.9.4 =
* Support for Jetpack Mobile theme without using extra plugins for featured image
* new comic archive dropdown code which better utilizes the menu order and tabbing
* don't display the 'comic chapters' title if it's empty

= 1.9.3 =
* started on making a clear price button for the buyprint
* cleaned up the ceo-admin.php from non-functional code and redid the hook actions
* fixed the list dropdown to display more then 5 and allow it to display on home page
* temporarily removed the change from checkbox to radio button in the quick edit for all comics

= 1.9.2 =
* Compatibility for widgets registering for older version of PHP, if you can though talk to your hosting to update your PHP version

= 1.9.1 =
* Widget updating messed up the navigation widgets execution of the navstyle.css this update fixes it

= 1.9 =
* Widgets updated for WordPress 4.3
* New Widget - "jump to" comic archive dropdown, shows list of comics from the current chapter to move-to 

= 1.8.7 =
* Fix for going back to 'all chapters' in the config after switching back to it.

= 1.8.6 =
* Confirmed 4.2.2 compatibility

= 1.8.5 =
* WordPress 4.1 compatibility mark
* removed all references to in_the_loop, was causing issues with some themes
* allowable change to which thumbnail size to use in the thumbnail widget

= 1.8.4 =
* Title fix for comics so that they display // quick patch, links break otherwise

= 1.8.3 =
* Rewrote the code to allow chapters in the URL using a different method
* removed the 'click to view larger image' text when lightbox is enabled
* added a new option to the comic editor in the toggle box, 'comic has map?' for those who want to make image maps for their comics
* moved an option on the navigation tab to it's appropriate location in the default nav section
* allow keyboard navigation jquery to work with the navigation widget and not just default nav


= 1.8.2 =
* Added option to disable the keyboard navigation script from running
* added error checking to the first and last in chapter get_terms function in the navigation.php
* edits to the archive-dropdown widget (yes, again) and default navigation archive-dropdown
* edit to the comic-archive list=1 shortcode ref: ps238.nodwick.com's archive
* added parent => 0 to stop the duplicating of info in the comic-archive


= 1.8.1 =
* Config section bug fix for saving the graphic navigation dropdown value

= 1.8 =
* Added some tabs in the comic - config
* Moved the post type name options to the 'archive' tab
* Added a couple of new options that allow you to change the word usage for "chapter" to something else
* resources tables coming from fetched file
* archive dropdown now shows select <name>  of the chosen name from the config
* do not display transcripts on the archive and search pages
* the column for the all comics now uses the name chosen for the taxonomy
* change the way the comment value is displayed in the navigation to better support disqus and other 3rd party comment systems
* Comic sidebars now default to being enabled on new installations
* option to add chapter_slug to the URL, replacement for the use of the custom post type permalinks plugin

= 1.7.7.1 =
* fixed bug where you couldn't save the facebook image size

= 1.7.7 =
* Added dropdown box to choose which thumbnail goes into the og:image for facebook to recognize
* adjusted the alternate color for the config and moved some alternates around in the css
* new shortcode [randcomic character=character-slug size=(thumbnail/large/full) chapter=chapter-slug name=post-name]

= 1.7.6 =
* Fixed the dropdown-archive widget to allow you to recheckmark certain options
* https://core.trac.wordpress.org/ticket/16863 bug problem still exists with the exclude in the dropdown-archive

= 1.7.5 =
* Changed the og:image size from thumbnail to full so facebook can love it better

= 1.7.4 =
* Filter the comic-display-area to be on the home landing page and single pages only.
* do not display the comic blog on alternate set posts pages
* fixed the hovertext entry for in the comic editor for hosts that can't handle syntax

= 1.7.3 =
* Added 'link to' box that makes whatever comic is there link to whatever URL you put in the input box
* Added comic archive dropdown shortcode [comic-archive-dropdown] so you can embed that into any post or page

= 1.7.2 =
* Added option to enable the default comic navigation to appear above the comic
* Added option to the archive dropdown widget to jump to the archive page instead of the first comic in the chapter
* Fixed bad sort in the archive dropdown widget and jumpto the first comic

= 1.7 / 1.7.1 =
* Now properly adds menu_order column to WPMS installs for chapter ordering
* Added toggle in archive chapter dropdown that let's you set it so that the count doesn't show
* changed width of ID column in the chapters editor section to 40px to account for higher iD numbers
* added option to allow comics to be associated with regular WordPress categories
* fixed bug where archive dropdown didn't properly display order (hopefully)
* added before- and after-comic-navigation action locations to place new buttons before and after to go along with the inside- one

= 1.6.2 =
* Verifying Compatibility with 3.9 WordPress

= 1.6.1 =
* Attempt to fix the language text domain and directory

= 1.6 =
* Can select which chapter appears on the home page (or all)
* removed click comic for larger version text

= 1.5.9 =
* Added option to disable the rewrite rules regeneration

= 1.5.8.1 =
* Add input box for changing the width of the media url as comic

= 1.5.8 =
* Use media URL as the comic (still use featured image as the thumbnail for it)

= 1.5.7.1 =
* Apostrophy fix for the keynav.js file

= 1.5.7 =
* Fix for the mishit of thumbnail widget messing up the page if not to show on the same page checkmark is done
* Fix for the keyboard navigation if someone wants to type a comment and use arrows in there

= 1.5.6 =
* Changed the 'chapter' select to radio instead of checkbox, can only have one chapter selected.
* Added an error message when editing a post that will display if the slug of the comic is numerical.

= 1.5.5 = 
* JS, added keyboard navigation .js file - auto-on always
* the thumbnail widget when you have multiple thumbs showing now has the wrapper on the outside and individuals inside of that wrapper

= 1.5.4 =
* CSS: removed the padding in #comic-foot and on #comic

= 1.5.3 =
* Traverse comic chapters with the previous/next buttons (option) in navigation tab.
* Added filter by columns on the comic - (all) comics page.
* The name to show all of the comics is now "all comics" instead of just "comic"

= 1.5.2 =
* Welcome back flash comics.

= 1.5.1 =
* Fixed a bunch of extra /slashes that were after plugin_path and plugin_url - thanks Eric Merced

= 1.5 =
* Buy Print/Original shortcode & buttons with IPN to Paypal
* Added Directions and changed the 'set featured image' to say 'Set Comic/Featured Image' in the comic editor.
* fixed plugin path/url code so if someone changes the wp-content directory it will adjust appropriately
* allow ?latest= to be slug, thanks @gladius2metal

= 1.4.5 =
* Added Motion Artist comics default CSS to the comiceasel.css
* removed erroneous quote in the admin-meta
* removed extra padding in the #comic-foot in the comiceasel.css
* major fix to admin-meta that was causing screen glitch for editor


= 1.4.4 =
* Made a filter to able to change the arguments for the home page comic and mini nav display AND nav widget
* added text-align: center; to #sidebar-over-comic and #sidebar-under-comic, can be overridden with !important
* made a filter for $transcript so it can be htmlspecialchars_decode() if you want to add html to the transcript
* fixed some stray text strings so they can be translated in the admin-meta.php
* permalink / dates now work with the comic post type
* removed title= in the comic calendar widget, which fixes the error messages
* added p.comic-thumbnail-in-archive CSS element to handle the section in the archive/search pages of the thumbnail output
* removed a couple uneeded links in the config

= 1.4.3 = 
* Support for Jetpack's Publicize and shortlinks for comics.
* Change menu position to 6 if Jetpack comics is activated so they don't overwrite each other.
* Fixed some localization strings being the wrong designation
* added some extra classes to the nav buttons in the default nav so it can be skinned properly
* sep. the comic_display_comic() function into individual parts so it isn't as lengthy to read
* don't check for navigate only chapters button is enabled when there is an option for it already on the random button
* removed w3 total cache transition post cache clear due to new change with w3 total cache

= 1.4.2 =
* shortcodes: fixed the 'ordering' of the thumbnail=1 in the comic-archive for list=0
* import: used proper site url find for is_multisite() installs - hopefully
* redirect: adding &comment to the /?latest=# line like /?latest=#&comment will make it add the #respond to the url line to go straight to the comments section
* shortcodes: fixed the 'ASC' 'DESC' display of thumbnails on the list_all list=0 (see first fix in 1.4.2, same thing just fixed the fix)
* CSS: changed the archive pages list-wrap's to be width: auto; to auto determine size available
* Jetpack Photon support, if Photon is enabled, all comic images will be served from the WordPress CDN saving you bandwidth.

= 1.4.1 =
* Remove testing code for thumbnails in the related comics section 
* changed facebook display function to social_meta and add some twitter meta to it - in testing
* shuffle some logic around in the casthover widget so it doesn't display title if there are no cast members set

= 1.4 =
* Added support for 'motion artist' comics.   Read documentation at comiceasel.com
* Fixed some visual issues on the comic - comics page, where the thumbnail was being cut off
* made the admin-editor.css file enqueue on the comics list page as well
* added 'this day in history' to the thumbnail widget, needs testing

= 1.3.13 =
* Added the random comic in navigation to work like the default one if set to chapter only
* Added some arguments to the ceo_comic_archive_jump_to_chapter() function

= 1.3.12 =
* make it so the chapter dropdown doesn't show empty chapters it just doesn't work
* check for 404 pages for the thumbnail casthover and comblogpost widgets and do not execute if it's a 404 page, or even page
* added constants-checking to disable features for developers who do not want their users have access
* option to make the random button stay within the same chapter/story
* cleaned up the navigation.php file, removed the 'syndication' part since it's not supposed to be there
* removed unused code from the displaycomic.php file
* redid displaycomic logic and added a whole bunch of error checking


= 1.3.11 =
* Option added to the thumbnail widget to set the thumbnail to not display on posts that the thumbnail displays (used to be always) now it default as off
* toggle in comic post editor to allow [all] comics attached to the post to display for the day full sized with or without a jquery page flipper
* COME ON Read the line above! It says MULTI COMIC per day support, not only that it's PER comic post NOT an all or nothing thing.


= 1.3.10 =
* removed some taxonomies from having their own feed, caused search engines to freak out
* replaced some home_url with admin_url for admin location links in the admin_meta
* restored the protect/unprotect but renamed them ceo_protect ceo_unprotect, wp_reset_query was having 'issues'
* added injection of the comic-easel version number as a meta tag for the <head> section
* fixed some injection code where it was returning at the wrong time for the post-info
* chapter= argument now works inside cast-page so that you can seperate the casts between different comics


= 1.3.9 =
* New widget, which displays mini thumbnails with hovercards of the cast members who are in the current comic - courtesy of Chris Maverick.   
* Fixed navigation issues, added another option to navigation widget; It no longer erases the titles when clicking save on first time adding it to the sidebar.
* Cleaned up some coding in all of the other widgets. Replaced most of the protect() unProtect() with wp_reset_query().
* Comic blog post widget now has an Ordering based on the option in the config.  

= 1.3.8 =
* Revamped the cast-page shortcode, in tables now, also shows most recent comic the character was in, cast-page now accepts order=(asc/desc) and limit=# arguments documentation now available at comiceasel.com

= 1.3.7 =
* New debug screen for variables and system information. (for me to help people with mainly)
* New option to enable transcripts to appear at the bottom of posts if the transcript exists instead of using the shortcode.  Disable if you want to use the shortcode instead.
* bug fix for default values not setting when plugin updates, if the option for transcripts is enables in the config disable use of the shortcode [transcript] while it's active

= 1.3.6 =
* Introducing Comic Easel - Import  (comic -> import in the wp-admin)

= 1.3.5 =
* Added multi thumbnail plugin coding so you can have 2 images per comic, one teaser image used  in the thumbnail widget and wherever else you code it in.
* CSS Adjustments to some of the navigation images
* added esc_attr checks to the thumbnail widget for extra security


= 1.3.3 =
* added new list= to the shortcode for [comic-archive list=3] will show yearly archive of comics, all on one page

= 1.3.2 =
* Added new list= to the shortcode for [comic-archive list=2] will show a yearly archive of comics split up into linkable pages

= 1.3 =
* Chapter Order Fields fixed

= 1.2 =
* Attempt at a navigation fix for the widget for front page ASC/DESC changes
* Also fixed (hopefully) the name change after saving the widget

= 1.1 =
* Added option to allow making the first comic appear on the home page
* the comic's blog post now will search for content-comic.php in the theme/child themes directory and use that if it exists

= 1.0.19 = 
* Fixed problem with tags and the archive
* reverted previous change to not showing chapters that were empty in the archive dropdown
* not flushing wp_rewrite on deactivation - should set the permalinks properly now on activation

= 1.0.16 =
* Added #blogheader div that appears under the comic's blog post on the home page.

= 1.0.14 =
* Made it so that the navigation widget shows up whether the comic sidebars are active or not.  They should work in any sidebar now.
* added function ceo_in_comic_category() for a conditional statement to check if the page is in the comic category

= 1.0.13 =
* Some additions to the language code, possible fox for the undefined problem with archive comic post types, various css fixes

= 1.0.12 =
* Never program while mad at the world ;/ apparently you make some mistakes in backwards compatibility.

= 1.0.11 =
* Removed URLRewrite /comic/#date#/ code since it was causing behavior problems
* Fixed the click to next and mini navigation to navigate per the setting in the config all chapters or just in chapter

= 1.0.10 = 
* Fix for RSS feeds, the problem was the action hook for it
* update: possible fix for clearing cache when custom post type is published with w3 total cache

= 1.0.9 =
* Use hovertext as well as comic-hovertext in the meta fields for those coming from ComicPress

= 1.0.8 =
* Bug fixes for shortcodes, and placement of shortcodes.

= 1.0.7 =
* Chapter ordering is now part of the plugin, if you see any errors report them, deactivate the plugin and reactivate.

= 1.0.5 =
* Chapter Navigation (prev/next chapter)
* Various little bug fixes here and there

= 1.0.4 =
* Fixed wrong function in filter for archive
* added option for turning off the mininav if it's implemented
* fixed the mininav to not be enabled of on the home page the comic is diabled

= 1.0.3 =
* Navigation Widget, Calendar widget, bug fixes and new code for navigating in chapters/all.
* New options for setting the thumbnail size for various locations that use thumbnails

= 1.0.2 =
* Added Sidebar generators for no matter what theme you use.
* Added the Navigation Widget, which replaces the default navigation, it shares the same skinning as ComicPress and will often times be able to use the navstyle from ComicPress.

= 1.0.1 =
* Updated: 05/26/2012 12:25am Pacific
- Made the prev/next link rel's properly navigate for comic posts

Updated: 05/26/2012 5:25pm Pacific
* Sidebar locations for the comic-area


== Upgrade Notice ==
= 1.0.16 =
* You should go to settings -> permalinks and click save again.




