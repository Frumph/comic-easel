=== Comic Easel ===
Contributors: frumph
Tags: comiceasel, easel, webcomic, comic, webcomic
Requires at least: 3.0
Tested up to: 3.4
Stable tag: 1.0.7
Donate link: http://frumph.net
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Comic Easel allows you to post webcomics to your theme.


== Description ==

Tech Support Forum: [Frumph.NET Forums](http://forum.frumph.net/ "The Forums for Frumph.NET")

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

- `[comic-archive list=0/1 chapter=# thumbnail=0/1]` Display a list of your comics by individual chapters or all.
* list=0 (default) - All chapters, not in parent->child relationship
* chapter=# if list=0 and chapter=# (# = chapter ID number) do a singular view
* list=1 if list=1 do it for series that has parent->child book->chapter (chapter= will not work)
* thumbnail=1 display the thumbnail of the first post it finds 
- `[cast-page]` Display a list of all of your characters, how many comics they were in and when they first appeared

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

= Adding the Comic area sidebars =

Sidebars for Comic Easel are added automatically since 05/28/2012 They should appear above all of your other sidebars in the widget panel.


== Frequently Asked Questions ==

= The permalinks are not working to go to the comic =

Go to your settings -> permalinks and just click save, the wp_rewrite will refresh.  You need to go to the settings -> permalinks if you ever upgrade enable or disable the Comic Easel plugin.

= Where is Comic Easel's navigation widget? =

The comic navigation widget is only seen if you have the comic sidebar's enabled; even then it only works in the comic sidebars themself, nowhere else.


== Changelog ==
= 1.0.7 =
Chapter ordering is now part of the plugin, if you see any errors report them, deactivate the plugin and reactivate.

= 1.0.5 =
Chapter Navigation (prev/next chapter)
Various little bug fixes here and there

= 1.0.4 =
Fixed wrong function in filter for archive
added option for turning off the mininav if it's implemented
fixed the mininav to not be enabled of on the home page the comic is diabled

= 1.0.3 =
Navigation Widget, Calendar widget, bug fixes and new code for navigating in chapters/all.
New options for setting the thumbnail size for various locations that use thumbnails

= 1.0.2 =
Added Sidebar generators for no matter what theme you use.
Added the Navigation Widget, which replaces the default navigation, it shares the same skinning as ComicPress and will often times be able to use the navstyle from ComicPress.

= 1.0.1 =
Updated: 05/26/2012 12:25am Pacific
- Made the prev/next link rel's properly navigate for comic posts

Updated: 05/26/2012 5:25pm Pacific
- Sidebar locations for the comic-area


== Upgrade Notice ==
= 1.0.7 =
You MAY need to deactivate the plugin and reactivate it.  Remove the term order additional plugin (if you have it), it's no longer needed!

= 1.0.5 =
Don't forget to ask for features for 1.0.6!

= 1.0.4 =
To make options work that are new, you save the tab that the option is on.

= 1.0.3 = 
Need to RESET CONFIG OPTIONS - Navigation Widget changes, Calendar Widget addition.

= 1.0.1 =
Additions and fixes of 1.0 - reinstall it.




