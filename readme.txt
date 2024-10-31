=== Page and Post Lister ===
Contributors: junlee
Donate link: http://www.codesandgraphics.com/cg-products/page-and-post-lister/#donate
Tags: page, post, lister, viewer, scanner, utility, editing, seo, google xml sitemap
Requires at least: 3.0.0
Tested up to: 3.3.1
Stable tag: 1.2.1

A post and page utility displaying ALL posts/pages in a single view. Useful for sites with large volume of content that are regularly updated.

== Description ==

A post and page utility that allows the display of ALL posts or pages at once in a single view. This is useful for websites that has large volume of posts or pages, where content are regularly updated.

Visit <a href="http://www.codesandgraphics.com/cg-products/page-and-post-lister">http://www.codesandgraphics.com/cg-products/page-and-post-lister</a> for the plugin site.

**Common Columns for Pages and Posts**

* **Google XML Sitemap toggling (NEW!)** at the level of individual posts and pages
* edit link
* post ID
* permalink
* author
* modified date
* search engine indexing
* comment toggling and **comment count (NEW!)**
* preview link
* delete link
* toggle links are ajax driven and does not require page refresh

<b>Additional Column for Pages</b>

* template

<b>New Features Not Found on the Default WordPress Post/page Browser</b>

* display of multiple information for all titles at once
* Toggle Google Indexing by adding meta tags on selected posts (robots,googlebot/noindex,noarchive,nosnippet)
* Batch operation (change status to publish, draft, future, private; delete, comment toggling, google indexing)
* display of ID and permalink
* display of post grouped by multiple categories
* display of Google XML Sitemap Status

Applications:

* Ideal for checking the search engine indexing status of posts and pages, very useful in SEO applications
* Ideal for editing contents of websites with large volume of content that make it difficult to locate a title using the default WordPress posts and pages browser.
* Ideal for scanning important information on multiple titles at once
* Ideal for developers who work on specific post IDs and permalinks
* Ideal for doing batch operations on several titles at ones (delete, change status, toggle commenting and google indexing)


== Installation ==

1. Upload page-and-post-lister to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. In the admin page, use the Plugin by clicking on the link "Post Lister" under the POST MENU, or "Page Lister" under the PAGE MENU

== Screenshots ==

1. Page Lister
2. Post Lister
3. Admin Menu 

== Changelog ==
= 1.2.1 =
* 31-October-2012 - Corrected the "cannot redeclare class cg_pages" error on WP 3.3

= 1.2.0 =
* 7-October-2011 - revised script to use "$wpdb->" on several MYSQL calls to address reported issues where PAGES are not shown on several occassions.

= 1.1.1 =
* 6-September-2011 - corrected column layout error when Google XML Sitemap is not installed

= 1.1.0 =
* added support for Google XML Sitemap plugin by Arne Brachhold; toggling is at the level of posts and pages only and not by category.
* added comment count 
* enhanced html markup on ajax toggles

= 1.0.4 =
* versioning adjustments on the WP Repository

= 1.0.3 =
* renamed folder from the original `cg-pp-lister` to the WordPress given `page-and-post-lister`

= 1.0.2 =
* Minor code tweaks.

= 1.0 =
* First release.


